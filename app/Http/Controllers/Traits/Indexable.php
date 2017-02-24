<?php

namespace App\Http\Controllers\Traits;

use App\Facades\Relations;
use Illuminate\Support\Collection;

trait Indexable
{
    protected function generateIndex($entities, $columns)
    {
        if ($entities->isEmpty()) {
            return [];
        }
        $class = get_class($entities->first()->getModel());
        $wantedRelations = [];
        $wantedRelations['total'] = collect();
        $result = [];
        foreach ($columns as $key => $filters) {
            $column = key($filters);
            $relations = Relations::classColumnToRelations($class, $column);
            if (isset($filters[$column]['columns']) && !$filters[$column]['columns']->isEmpty()) {
                foreach ($relations as $relation) {
                    $notWanted = false;
                    if (isset($filters[$column]['columns']) && !$filters[$column]['columns']->isEmpty()) {
                        $notWanted = true;
                        foreach ($filters[$column]['columns'] as $c) {
                            $ccs = Relations::classColumnToRelations($class, $c);
                            foreach ($ccs as $cc) {
                                if (strpos($relation, $cc) !== false) {
                                    $notWanted = false;
                                }
                            }
                        }
                    }
                    if (!$notWanted) {
                        $wantedRelations[$key] = isset($wantedRelations[$key]) ? $wantedRelations[$key]->push($relation) : collect($relation);
                        $wantedRelations['total']->push($relation);
                    }
                }
            } else {
                $wantedRelations[$key] = collect();
                foreach ($relations as $relation) {
                    $wantedRelations[$key]->push($relation);
                    $wantedRelations['total']->push($relation);
                }
            }
        }
        $relations = $wantedRelations['total'];
        unset($wantedRelations['total']);

        $combinedRelations = [collect($relations->shift())];

        while (!$relations->isEmpty()) {
            $r1 = $relations->shift();
            $c = count($combinedRelations);
            for ($i = 0; $i < $c; $i++) {
                if (strpos($r1, $combinedRelations[$i]->first()) === 0) {
                    $combinedRelations[$i]->prepend($r1);
                    continue 2;
                } elseif (strpos($combinedRelations[$i]->first(), $r1) === 0) {
                    $combinedRelations[$i]->push($r1);
                    continue 2;
                }
            }
            $combinedRelations[] = collect($r1);
        }

        $id = Relations::classFieldToField($class, 'id');
        foreach ($entities as $entity) {
            $result[$entity->{$id}] = null;
            foreach ($combinedRelations as $cr) {
                $stack = collect([['entity' => $entity, 'pos' => 0]]);
                $r1 = $cr->first();
                $r1parts = explode('.', $r1);
                $r1max = count($r1parts);
                while (!$stack->isEmpty()) {
                    $entry = $stack->pop();
                    $currentR = implode('.', array_slice($r1parts, 0, $entry['pos'] + 1));
                    if ($entry['pos'] < $r1max) {
                        if ($cr->contains($currentR)) {
                            if ($currentR !== '') {
                                if (isset($result[$entity->{$id}][$currentR]) && $result[$entity->{$id}][$currentR] instanceof Collection) {
                                    if ($entry['entity']->{$r1parts[$entry['pos']]} instanceof Collection) {
                                        $result[$entity->{$id}][$currentR] = $result[$entity->{$id}][$currentR]->merge($entry['entity']->{$r1parts[$entry['pos']]}->all());
                                    } else {
                                        $result[$entity->{$id}][$currentR]->push($entry['entity']->{$r1parts[$entry['pos']]});
                                    }
                                } elseif (!isset($result[$entity->{$id}][$currentR]) && $entry['entity']->{$r1parts[$entry['pos']]} instanceof Collection) {
                                    $result[$entity->{$id}][$currentR] = $entry['entity']->{$r1parts[$entry['pos']]};
                                } else {
                                    $result[$entity->{$id}][$currentR] = collect([$entry['entity']->{$r1parts[$entry['pos']]}]);
                                }
                            } else {
                                $result[$entity->{$id}][$currentR] = collect([$entry['entity']]);
                            }
                        }
                        if ($r1parts[$entry['pos']] !== '') {
                            $es = $entry['entity']->{$r1parts[$entry['pos']]};
                        } else {
                            $es = $entry['entity'];
                        }
                        if (!$es instanceof Collection) {
                            $es = collect([$es]);
                        } else {
                            $es = $es->reverse();
                        }
                        foreach ($es as $e) {
                            $stack->push(['entity' => $e, 'pos' => $entry['pos'] + 1]);
                        }
                    }
                }
            }
        }
        return [$result, $wantedRelations];
    }
}