<?php

namespace IvanoMatteo\LaravelCodiceFiscale;

use DateTime;
use IvanoMatteo\CodiceFiscale\CodiceFiscale;
use IvanoMatteo\CodiceFiscale\CodicefiscaleException;


class LaravelCodiceFiscale
{

    private $cache = [];

    private $filedNames = [
        'name' => 1,
        'familyName' => 1,
        'dateOfBirth' => 1,
        'sex' => 1,
        'cityCode' => 1,
    ];

    private function parse($cf_str)
    {
        $cf_str = strtoupper(trim($cf_str));

        $res = $this->cache[$cf_str] ?? null;

        if (isset($res['err']) || isset($res['cf'])) {
            return $res;
        }

        try {
            $cf = CodiceFiscale::parse($cf_str);

            $this->cache[$cf_str] = [
                'cf' => $cf,
            ];
        } catch (CodicefiscaleException $ex) {
            $this->cache[$cf_str] = [
                'err' => __('laravel-codice-fiscale::codfisc.' . $ex->getMessageCode()),
            ];
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

            $map = []; // create parameters map
            foreach ($parameters as $p) {
                $p = explode('=', $p);
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
            if ($codfisc && !$attr) { // try with current field name
                if (isset($this->filedNames[$attribute])) {
                    $attr = $attribute;
                }
            }

            if (($attr || $codfisc) && (!$attr || !$codfisc)) {
                throw new \Exception('codfisc validator: arguments attr and codfisc must be specified together');
            }

            // get request data
            $reqData = $validator->getData();

            $cf = $this->parse($codfisc ? $reqData[$codfisc] : $value);

            if (!isset($cf['err'])) {
                $cf = $cf['cf'];

                if ($attr) {
                    $map = [$attr => $attribute];
                }

                $matchData = array_intersect_key($reqData, array_flip($map));
                $errs = $cf->validate($matchData, $map, true);

                if (!empty($errs)) {
                    if ($attr) {
                        $msg .= __('laravel-codice-fiscale::codfisc.field-do-not-match');
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
                            $err_fields .= __('laravel-codice-fiscale::codfisc.' . $f);
                        }

                        $msg .= $err_fields . ') ' . __('laravel-codice-fiscale::codfisc.do-not-match');
                    }
                }
            } else {
                if ($attr) {
                    $msg .= __('laravel-codice-fiscale::codfisc.impossible-to-match') . ': ' . $cf['err'];
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
