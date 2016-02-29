graph berlussimo
{
//ratio = 0.71; //1.41
//size = 10;
layout=neato;
//size="11.7,8.27";
overlap=false;
splines=true;
pack=true;
//start="MIETVERTRAG";
sep=0.3;
orientation=landscape

node [shape = rectangle];
edge[len=2]
@foreach($relationships as $relationship)
{!! $relationship[0] !!} -- {!! $relationship[1] !!}[label="{!! $relationship[2] !!}"];
@endforeach
}