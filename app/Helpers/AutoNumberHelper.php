<?php
namespace App\Helpers;

use App\Models\Employees;
use App\Models\WhistleblowingReports;
use App\Models\RiskReports;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AutoNumberHelper
{
    public static function initGenerateNumber($prefix, $date = '')
    {
        $data = [];

        if ($prefix == null || $prefix == '') {
            return response()->json(['status' => 'error', 'data' => '', 'message' => 'Prefix should exist!']);
        } else {
            switch ($prefix) {
                case "EM":
                    $data = ['class' => Employees::class , 'field' => 'code', 'prefix' => $prefix];
                    break;
                case "WB":
                    $data = ['class' => WhistleblowingReports::class , 'field' => 'document_number', 'prefix' => $prefix];
                    break;
                case "RK":
                    $data = ['class' => RiskReports::class , 'field' => 'document_number', 'prefix' => $prefix];
                    break;
                default:
                    echo "Your favorite color is neither red, blue, nor green!";
            }
        }

        return self::generateNumber($data, $date);
    }

    /**
     * Get next code from a name
     *
     * @param string $prefix
     * @param string $name
     */
    public static function getNextCode($prefix, $name)
    {
        $data = [];

        $validator = Validator::make(
            ['prefix' => $prefix, 'name' => $name],
            ['prefix' => 'required', 'name' => 'required'],
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()->all()
            ], 422);
        }

        switch ($prefix) {
            default:
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or unregistered code prefix provided. Cannot process request.',
                ], 400);
        }

        return response()->json(['code' => self::generateCode($data)]);
    }

    private static function generateNumber($params, $date)
    {
        $now = Carbon::now();
        $prefixSize = (strlen($params['prefix'])) + 10;

        $month_param = $now->month;
        $year_param = $now->year;

        if ($date != '') {
            $expl_date = explode('-', $date);
            if (count($expl_date) > 1) {
                $month_param = $expl_date[1];
                $year_param = $expl_date[0];
            }
        }

        $prefix = $params['prefix'];
        $prefix .= $year_param . sprintf('%02d', $month_param);

        $data = $params['class']::whereRaw('LENGTH(' . $params['field'] . ') = ?', $prefixSize)
            ->where($params['field'], 'ilike', $prefix . '%')->orderBy('id', 'DESC')
            ->first();

        if ($data == null) {
            $prefix .= sprintf('%04d', 1);
        } else {
            $repeat = true;
            $last = substr($data[$params['field']], -4);
            $last = ++$last;

            $new = sprintf('%04d', $last);
            while ($repeat)
            {
                $data = $params['class']::where($params['field'], $prefix . $new)->first();

                if ($data == null) {
                    $repeat = false;
                    $prefix .= sprintf('%04d', $new);
                } else {
                    $new = sprintf('%04d', ++$new);
                }
            }
        }
        return $prefix;
    }

    private static function generateCode($params)
    {
        $code = '';
        $field = $params['field'];
        $prefix = $params['prefix'];
        // TODO: Dynamically change code format based on general settings
        $width = 4;
        $padding = '0';
        $separator = '-';
        // $codeLength = (strlen($prefix)) + $width;

        $data = $params['class']::select($field)
            ->where($field, 'ilike', $prefix . $separator . '%')
            // ->whereRaw('LENGTH(' . $field . ') = ?', $codeLength) // Generate unique code where the string length is a significant part of its value (e.g., "DTC0001" != "DTC00001")
            ->orderBy($field, 'DESC')
            ->first();

        $format = '%'.$padding.$width.'d';
        if ($data == null) {
            $code = $prefix . $separator . sprintf($format, 1);
        } else {
            $last = substr($data[$field], -$width);
            $code = $prefix . $separator . sprintf($format, $last+1);
        }
        return $code;
    }

    public static function generateAutoCode($table, $column, $prefix, $length = 6)
    {
        $last = DB::table($table)
        ->where($column, 'like', $prefix.'%')
        ->orderBy($column, 'desc')
        ->value($column);

        if ($last) {
            $number = (int) str_replace($prefix, '', $last);
            $number++;
        } else {
            $number = 1;
        }

        return $prefix . str_pad($number, $length, '0', STR_PAD_LEFT);
    }
}
