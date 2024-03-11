<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Barber;
use App\Models\BarberPhotos;
use App\Models\BarberServices;
use App\Models\BarberTestimonial;
use App\Models\BarberAvailability;

class BarberController extends Controller
{
    private $loggedUser;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->loggedUser = auth()->user();
    }

    private function searchGeo($address) {
        $key = env('MAPS_KEY', null);

        $address = urlencode($address);
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.$address.'&key='.$key;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($ch);
        curl_close($ch);

        return json_decode($res, true);

    }

    public function list(Request $request){
        $array = ['error' => ''];

        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $city = $request->input('city');
        $offset = $request->input('offset');
        if(!$offset){
            $offset = 0;
        }

        if(!empty($city)){
            $res = $this->searchGeo($city);

            if(count($res['results']) > 0) {
                $lat = $res['results'][0]['geometry']['location']['lat'];
                $lng = $res['results'][0]['geometry']['location']['lng'];
            }
        } elseif(!empty($lat) && !empty($lng)){
            $res = $this->searchGeo($lat.','.$lng);

            if(count($res['results']) > 0) {
                $city = $res['results'][0]['formatted_address'];
            }
        } else {
            $lat = '-23.5630907';
            $lng = '-46.6682795';
            $city = 'Sao Paulo';
        }

        $barbers = Barber::select(Barber::raw('*, SQRT(
            POW(69.1 * (latitude - '.$lat.'), 2) +
            POW(69.1 * ('.$lng.' - longitude) * COS(latitude / 57.3), 2)) AS distance'))
            ->havingRaw('distance < ?', [10])
            ->orderBy('distance', 'ASC')
            ->offset($offset)
            ->limit(5)
            ->get();

        foreach($barbers as $bkey => $bvalue) {
            $barbers[$bkey]['avatar'] = url('media/avatars/'.$barbers[$bkey]['avatar']);
        }

        $array['data'] = $barbers;
        $array['loc'] = 'Sao Paulo';


        return $array;
    }


    // public function createRandom()
    // {
    //     $array = ['error' => ''];

    //     for ($q = 0; $q < 15; $q++) {
    //         $names = ['Tiago', 'Pablo', 'Pedro', 'Amanda', 'Leticia', 'Gabriel'];
    //         $lastnames = ['Silva', 'Souza', 'Diniz', 'Oliveira', 'Carvalho', 'Santos'];
    //         $services = ['Corte', 'Pintura', 'Aparacao', 'Enfeite'];
    //         $services2 = ['Cabelo', 'Unha', 'Pernas', 'Sobrancelhas'];

    //         $depos = [
    //             'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
    //             'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
    //             'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
    //             'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
    //             'Lorem Ipsum is simply dummy text of the printing and typesetting industry.'
    //         ];

    //         $newBarber = new Barber();
    //         $newBarber->name = $names[rand(0, count($names) - 1)] . ' ' . $lastnames[rand(0, count($lastnames) - 1)];
    //         $newBarber->avatar = rand(1, 4) . '.png';
    //         $newBarber->latitude = '-23.5' . rand(0, 9) . '30907';
    //         $newBarber->longitude = '-46.6' . rand(0, 9) . '82795';
    //         $newBarber->save();

    //         $ns = rand(3, 6);

    //         for ($w = 0; $w < 4; $w++) {
    //             $newBarberPhoto = new BarberPhotos();
    //             $newBarberPhoto->id_barber = $newBarber->id;
    //             $newBarberPhoto->url = rand(1, 5) . '.png';
    //             $newBarberPhoto->save();
    //         }

    //         for ($w = 0; $w < $ns; $w++) {
    //             $newBarberService = new BarberServices();
    //             $newBarberService->id_barber = $newBarber->id;
    //             $newBarberService->name = $services[rand(0, count($services) - 1)] . ' de ' . $services2[rand(0, count($services2) - 1)];
    //             $newBarberService->price = rand(1, 99) . '.' . rand(0, 100);
    //             $newBarberService->save();
    //         }

    //         for($w=0;$w<3;$w++){
    //             $newBarberTestimonial = new BarberTestimonial();
    //             $newBarberTestimonial->id_barber = $newBarber->id;
    //             $newBarberTestimonial->name = $names[rand(0, count($names)-1)];
    //             $newBarberTestimonial->rate = rand(2,4).'.'.rand(0,9);
    //             $newBarberTestimonial->body = $depos[rand(0, count($depos)-1)];
    //             $newBarberTestimonial->save();
    //         }

    //         for($e=0;$e<4;$e++){
    //             $rAdd = rand(7, 10);
    //             $hours = [];
    //             for($r=0;$r<8;$r++){
    //                 $time = $r + $rAdd;
    //                 if($time < 10){
    //                     $time = '0'.$time;
    //                 }
    //                 $hours[] = $time.':00';
    //             }
    //             $newBarberAvail = new BarberAvailability();
    //             $newBarberAvail->id_barber = $newBarber->id;
    //             $newBarberAvail->weekday = $e;
    //             $newBarberAvail->hours = implode(',', $hours);
    //             $newBarberAvail->save();
    //         }
    //     }

    //     return $array;
    // }

}