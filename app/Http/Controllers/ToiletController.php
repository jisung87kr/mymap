<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Toilet;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ToiletController extends Controller
{
    public $toilet;

    public function __construct(Toilet $toilet)
    {
        $this->toilet = $toilet;
    }

    public function index($city=null)
    {
        if(is_null($city)){
            $toilets = [];
        } else {
            $toilets = $this->toilet::where('시', 'LIKE', "{$city}%")->get();
        }

        $data = [
            'search' => $city,
            'result' => $toilets
        ];
        return $this->responseIndex($data);
    }

    public function update(Toilet $id, Request $request)
    {
        $id->update([
            '위도' => $request->위도,
            '경도' => $request->경도,
        ]);

        return response()->json(
            [
                'success' => 'updated',
                'response' => $id
            ],
            201,
            [],
            JSON_PRETTY_PRINT);
    }

    public function responseIndex($data)
    {
        $toilets = $data['result'];
        $city = $data['search'];
        return view('index', compact('toilets', 'city'));
    }

    public function setting()
    {
        if(!Schema::hasColumn('toilets', 'id')){
            Schema::table('toilets', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        }

        if(!Schema::hasColumn('toilets', '주소')){
            Schema::table('toilets', function (Blueprint $table) {
                $table->text('주소')->default('');
            });
        }

        if(!Schema::hasColumn('toilets', '도')){
            Schema::table('toilets', function (Blueprint $table) {
                $table->string('도', 100)->default('');
            });
        }

        if(!Schema::hasColumn('toilets', '시')){
            Schema::table('toilets', function (Blueprint $table) {
                $table->string('시', 100)->default('');
            });
        }

        $this->toilet::chunk(200, function($toilets){
            foreach ($toilets as $toilet) {
                $addr = $toilet->소재지도로명주소 ? $toilet->소재지도로명주소 : $toilet->소재지지번주소;
                $addrArr = explode(" ", $addr);
                $province = isset($addrArr[0]) ? $addrArr[0] : '';
                $city = isset($addrArr[1]) ? $addrArr[1] : '';
                $toilet->update([
                    '주소' => $addr,
                    '도' => $province,
                    '시' => $city,
                ]);
            }
        });

        return 'hello';
    }
}
