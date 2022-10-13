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

            $codfisc  = null;
            $attr  = null;
            $msg = null;

            $map = []; // create parameters map
            
            foreach ($parameters as $i => $p) {
                if ($i === 0 && strpos($p, '=') === false) {
                    $codfisc = $p;
                }
                if ($i === 1 && strpos($p, '=') === false) {
                    $attr = $p;
                } else {
                    $p = explode('=', $p);
                    if(isset($p[1])){
                        $map[$p[0]] = $p[1];
                    }
                }
            }

            if ($codfisc && !$attr) { // try with current field name
                $attr = $attribute;
            }
            //dump(compact('attribute', 'codfisc', 'attr'));

            if ($attr && empty($this->filedNames[$attr])) {
                throw new \Exception("unknown attr: $attr");
            }

            // get request data
            $reqData = $validator->getData();

            $cf = $this->parse($codfisc ? $reqData[$codfisc] : $value);

            if (!isset($cf['err'])) {
                $cf = $cf['cf'];

                if ($attr) {
                    $map = [$attr => $attribute];
                }

                if (!empty($map)) {

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
                }

            } else {
                if ($attr) {
                    if (config('laravel-codice-fiscale.impossible-to-match')) {
                        $msg .= __('laravel-codice-fiscale::codfisc.impossible-to-match') . ': ' . $cf['err'];
                    }
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
