<?php

namespace App\Http\Controllers;

use App\Models\Option;
use App\Models\Reserve;
use App\Models\Service;
use App\Models\Time;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ReserveController extends Controller
{
    private function sum_times()
    {
        $i = 0;
        foreach (func_get_args() as $time) {
            sscanf($time, '%d:%d', $hour, $min);
            $i += $hour * 60 + $min;
        }
        if ($h = floor($i / 60)) {
            $i %= 60;
        }
        return sprintf('%02d:%02d:%02d', $h, $i, '00');
    }

    public function reserve(Request $request)
    {
        if (empty($request->bearerToken())) {
            $req = new Request($request->all());
            return AuthController::createOTP($req);
            
        } else {
            //barbershop details
            $start = Option::where('key', 'start')->first()->value;
            $end = Option::where('key', 'end')->first()->value;
            $service = Service::find($request->service_id);
            $freetimes = $this->freeTimes($start, $end, $request->date, $service->duration);

            return $freetimes;
            $reserved = Reserve::create([
                'date' => $request->date,
                'start' => $freetimes[6]['start'],
                'end' => $freetimes[6]['end'],
                'user_id' => $request->user()->id,
            ]);
            return $reserved;
        };
    }

    private function freeTimes($start, $end, $date, $duration)
    {
        $strt = $start;
        $loop = true;
        $list = [];
        while ($loop) {
            $endtime = $this->sum_times($strt, $duration);
            if ($endtime >= $end) {
                $loop = false;
                break;
            }
            $checkFields = $this->checkFilleds($date, $strt, $endtime);
            if ($checkFields['state']) {
                $list[] = ['start' => $strt, 'end' => $endtime];
                $strt = $endtime;
            } else
                $strt = $checkFields['start'];
        }
        return $list;
    }

    private function checkFilleds($date, $start, $end)
    {
        $filleds = $this->filleds($date);
        $result = ['state' => true];
        foreach ($filleds as $key => $filled) {
            if ((($filled['start'] <= $start && $start < $filled['end']) || ($filled['start'] < $end && $end <= $filled['end']))) {
                $result = ['state' => false, 'start' => $filled['end']];
                break;
            } else if ((($start <= $filled['start'] && $filled['start'] < $end) || ($start < $filled['end'] && $filled['end'] <= $end))) {
                $result = ['state' => false, 'start' => $filled['end']];
                break;
            }
        }
        return $result;
    }


    private function filleds($date)
    {
        $reserveds = Reserve::where('date', $date)->get();
        $list = [];
        foreach ($reserveds as $reserved) {
            $list[] = ['start' => $reserved->start, 'end' => $reserved->end];
        }
        return $list;
    }
}
