<?php

namespace IvanoMatteo\LaravelCodiceFiscale;

use DateTime;
use IvanoMatteo\CodiceFiscale\CodiceFiscale;
use IvanoMatteo\CodiceFiscale\CodicefiscaleException;


class LaravelCodiceFiscale
{

    private $cache = [];
    private $filedNames = [
        'name' => 'Name',
        'familyName' => 'Family Name',
        'dateOfBirth' => 'Date of Birth',
        'sex' => 'Sex',
        'cityCode' => 'City Code',
    ];

    private function parse($cf_str, $century = null)
    {
        $cf_str = strtoupper(trim($cf_str));

        $res = $this->cache[$cf_str] ?? null;
        if (
            isset($res['err']) ||
            (isset($res['cf']) &&
                !(isset($century) && !isset($res['century'])))
        ) {
            return $res;
        }

        try {
            $cf = CodiceFiscale::parse($cf_str, $century);

            $this->cache[$cf_str] = [
                'cf' => $cf,
                'century' => $century
            ];
        } catch (CodicefiscaleException $ex) {

            switch ($ex->getCode()) {
                case CodicefiscaleException::FORMAT:
                    $this->cache[$cf_str] = [
                        'err' => __('invalid format'),
                    ];
                    break;
                case CodicefiscaleException::DATE_OF_BIRTH:
                    $this->cache[$cf_str] = [
                        'err' => __('date of birth do not match')
                    ];
                    break;
                case CodicefiscaleException::CONTROL_DIGIT:
                    $this->cache[$cf_str] = [
                        'err' => __('control digit do not match')
                    ];
                    break;
                default:
                    $this->cache[$cf_str] = [
                        'err' => __($ex->getMessage())
                    ];
            }
        }

        return $this->cache[$cf_str];
    }



    public function registerValidator()
    {

        \Validator::extend('codfisc', function ($attribute, $value, $parameters, $validator) {

            //dd($attribute);
            //dd($validator->attributes());
            //dd(get_class_methods($validator));

            $msg = null;

            $map = [];
            foreach ($parameters as $p) {
                $p = explode('/', $p);
                $map[$p[0]] = $p[1] ?? $p[0];
            }

            $codfisc  = null;
            if (isset($map['cf'])) {
                $codfisc = $map['cf'];
                unset($map['cf']);
            }
            $attr  = null;
            if (isset($map['attr'])) {
                $attr = $map['attr'];
                unset($map['attr']);
            }
            if($codfisc && !$attr){
                if(isset($this->filedNames[$attribute])){
                    $attr = $attribute;
                }
            }

            if(($attr || $codfisc) && (!$attr || !$codfisc) ){
                throw new \Exception('codfisc validator: arguments attr and codfisc must be specified together');
            }

            $reqData = $validator->getData();


            $century = null;
            $dob = $reqData[$map['dateOfBirth'] ?? null] ?? null;
            if ($dob) {
                try {
                    $tmp = new \DateTime($dob);
                    $century = (int) $tmp->format('Y');
                } catch (\Exception $ex) {
                }
            }

            $cf = $this->parse($codfisc ? $reqData[$codfisc] : $value, $century);

            if (!isset($cf['err'])) {
                $cf = $cf['cf'];

                if ($attr) {
                    $map = [$attr => $attribute];
                }

                $matchData = array_intersect_key($reqData, array_flip($map));
                $errs = $cf->validate($matchData, $map, true);

                if (!empty($errs)) {
                    if ($attr) {
                        $msg .= __('do not match with fiscal code');
                    } else {

                        if (!empty($msg)) {
                            $msg .= ', ';
                        }
                        $msg .= '(';
                        $err_fields = '';
                        foreach ($errs as $f) {
                            if (!empty($err_fields)) {
                                $err_fields .= ', ';
                            }
                            $err_fields .= __($this->filedNames[$f]);
                        }

                        $msg .= $err_fields . ') ' . __('do not match');
                    }
                }
            } else {
                if ($attr) {
                    $msg .= __('impossible to match with fiscal code: ') . $cf['err'];
                } else {
                    $msg = $cf['err'];
                }
            }

            if ($msg) {
                $validator->addReplacer(
                    'codfisc',
                    function ($message, $attribute, $rule, $parameters) use ($msg) {
                        return \str_replace(':cf_error', $msg, $message);
                    }
                );
                return false;
            }

            return true;
        }, ':attribute :cf_error');
    }
}
