@inject('relations', '\App\Services\RelationsService')
<table class="striped responsive-table">
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
                if(isset($fields[$column]['columns']) && (!$fields[$column]['columns']->isEmpty() || !$fields[$column]['aggregates']->isEmpty())) {
                    echo '<br>[';
                    if(!$fields[$column]['aggregates']->isEmpty()) {
                        $last = $fields[$column]['aggregates']->last();
                        foreach ($fields[$column]['aggregates'] as $col)
                        {
                            echo  e(ucfirst($col));
                            if($last != $col || !$fields[$column]['columns']->isEmpty()) {
                                echo e(', ');
                            }
                        }
                    }
                    if(!$fields[$column]['columns']->isEmpty()) {
                        $last = $fields[$column]['columns']->last();
                        foreach ($fields[$column]['columns'] as $col)
                        {
                            echo  e(ucfirst($col));
                            if($last != $col) {
                                echo e(', ');
                            }
                        }
                    }
                    echo e(']');
                }
            @endphp
        </th>
    @endforeach
    </thead>
    <tbody>
    @php($id = $relations->classFieldToField($class, 'id'))
    @foreach( $index as $entity )
        <tr>
            @foreach($columns as $key => $fields)
                <td>
                    @php
                        $column = key($fields);
                        $rs = $relations->columnColumnToRelations($relations->classToColumn($class), $column);
                        foreach ($rs as $r) {
                            if($wantedRelations[$key]->contains($r)) {
                                $c = 0;
                                if(isset($fields[$column]['columns'])) {
                                    $c = $fields[$column]['columns']->count();
                                }
                                if($c > 1 || ($c == 0 && count($rs) > 1 && isset($entity[$r])))
                                    echo ucfirst($relations->classToColumn($relations->classRelationToMany($class, $r)[1])) . '<br>';
                                if(isset($fields[$column]['aggregates']) && !$fields[$column]['aggregates']->isEmpty()) {
                                    foreach ($fields[$column]['aggregates'] as $aggregate) {
                    @endphp
                    @include('shared.entities.aggregates.count', ['entities' => $entity[$r], 'aggregate' => $aggregate] )<br>
                    @php
                                    }
                                } else {
                    foreach ($entity[$r] as $value) {
                        if(isset($fields[$column]['fields']) && !$fields[$column]['fields']->isEmpty()) {
                            $last = $fields[$column]['fields']->last()['field'];
                            foreach ($fields[$column]['fields'] as $field)
                            {
                                $dbField = $relations->classFieldToField(get_class($value), $field['field']);
                                if(trim($value->{$dbField}) != '' && $last != $field['field']) {
                                    echo e($value->{$dbField} . ', ');
                                } elseif ($last == $field['field']) {
                                    echo e($value->{$dbField}) . '<br>';
                                }
                            }
                        } else {
                    @endphp
                    @include('shared.entity', ['entity' => $value] )<br>
                    @php
                                    }
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
