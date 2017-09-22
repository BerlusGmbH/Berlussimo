<?php

namespace App\Http\Controllers\Api\v1\Modules;

use App\Http\Controllers\Controller;
use App\Http\Requests\Legacy\DetailsRequest;
use App\Models\Details;
use DB;
use Illuminate\Http\Request;

class DetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param DetailsRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(DetailsRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $detail = Details::create(
                array_merge(
                    $request->only(
                        [
                            'DETAIL_INHALT',
                            'DETAIL_BEMERKUNG',
                            'DETAIL_NAME',
                            'DETAIL_ZUORDNUNG_TABELLE',
                            'DETAIL_ZUORDNUNG_ID'
                        ]
                    ),
                    [
                        'DETAIL_AKTUELL' => '1',
                        'DETAIL_ID' => Details::max('DETAIL_ID') + 1
                    ]
                )
            );
            return response()->json($detail);
        });
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Details $details
     * @return \Illuminate\Http\Response
     */
    public function show(Details $details)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Details $details
     * @return \Illuminate\Http\Response
     */
    public function edit(Details $details)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param DetailsRequest|Request $request
     * @param  \App\Models\Details $detail
     * @return \Illuminate\Http\Response
     */
    public function update(DetailsRequest $request, Details $detail)
    {
        $detail->update($request->only(['DETAIL_INHALT', 'DETAIL_BEMERKUNG']));
        return response()->json($detail);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DetailsRequest $request
     * @param  \App\Models\Details $detail
     * @return \Illuminate\Http\Response
     */
    public function destroy(DetailsRequest $request, Details $detail)
    {
        return [$detail->update(['DETAIL_AKTUELL' => '0'])];
    }
}
