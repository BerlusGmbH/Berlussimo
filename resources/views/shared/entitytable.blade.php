@inject('relations', '\App\Services\RelationsService')
<table class="striped">
    <thead>
    @foreach( $columns as $fields)
        <th>
            @php
                $column = key($fields);
                echo e(ucfirst($column));
                if(isset($fields) && isset($fields[$column]) && !$fields[$column]['fields']->isEmpty()) {
                    echo '<br>(';
                    $last = $fields[$column]['fields']->last()['field'];
                    foreach ($fields[$column]['fields'] as $field)
                    {
                        echo  e(ucfirst($field['field']));
                        if($last != $field['field']) {
                            echo e(', ');
                        } elseif ($last == $field['field']) {
                            echo e(')');
                        }
                    }
                }
                if(isset($fields) && isset($fields[$column]) && !$fields[$column]['columns']->isEmpty()) {
                    echo '<br>[';
                    $last = $fields[$column]['columns']->last();
                    foreach ($fields[$column]['columns'] as $col)
                    {
                        echo  e(ucfirst($col));
                        if($last != $col) {
                            echo e(', ');
                        } elseif ($last == $col) {
                            echo e(']');
                        }
                    }
                }
            @endphp
        </th>
    @endforeach
    </thead>
    <tbody>
    @php
        $wanted = [];
        foreach ($columns as $fields) {
            $column = key($fields);
            $rs = $relations->classColumnToRelations($class, $column);
            foreach ($rs as $r) {
                $notWanted = false;
                if(isset($fields[$column]['columns']) && !$fields[$column]['columns']->isEmpty()) {
                    $notWanted = true;
                    foreach($fields[$column]['columns'] as $co) {
                        $ccs = Relations::classColumnToRelations($class, $co);
                        foreach($ccs as $cc) {
                            if(strpos($r, $cc) !== false) {
                                $notWanted = false;
                            }
                        }
                    }
                }
                $wanted[$column][$r] = !$notWanted;
            }
        }
    @endphp
    @foreach( $entities as $entity )
        <tr>
            @foreach($columns as $fields)
                <td>
                    @php
                        $column = key($fields);
                        $rs = $relations->classColumnToRelations($class, $column);
                        $stack = [];
                        foreach ($rs as $r) {
                            if(!$wanted[$column][$r]) {
                                continue;
                            }
                            $parts = explode('.', $r);
                            $max = count($parts);
                            $stack = collect([['entity' => $entity, 'pos' => 0]]);
                            while(!$stack->isEmpty()) {
                                $entry = $stack->pop();
                                if($entry['pos'] == $max) {
                                    if(isset($fields) && isset($fields[$column]['fields']) && !$fields[$column]['fields']->isEmpty()) {
                                        $last = $fields[$column]['fields']->last()['field'];
                                        foreach ($fields[$column]['fields'] as $field)
                                        {
                                            $dbField = $relations->classFieldToField(get_class($entry['entity']), $field['field']);
                                            if(trim($entry['entity']->{$dbField}) != '' && $last != $field['field']) {
                                                echo e($entry['entity']->{$dbField} . ', ');
                                            } elseif ($last == $field['field']) {
                                                echo e($entry['entity']->{$dbField}) . '<br>';
                                            }
                                        }
                                    } else {
                    @endphp
                    @include('shared.entity', ['entity' => $entry['entity']] )<br>
                    @php
                        }
                    } else {
                        if($parts[$entry['pos']] !== '') {
                            $es = $entry['entity']->{$parts[$entry['pos']]};
                        } else {
                            $es = $entry['entity'];
                        }
                        if(!$es instanceof \Illuminate\Database\Eloquent\Collection) {
                            $es = [$es];
                        } else {
                            $es = $es->reverse();
                        }
                        foreach ($es as $e) {
                            $stack->push(['entity' => $e, 'pos' => $entry['pos'] + 1]);
                        }
                    }
                }
            }
                    @endphp
                </td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
