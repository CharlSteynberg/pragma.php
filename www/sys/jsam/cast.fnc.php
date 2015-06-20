<?

// def :: parse - extends: `jsam`
// --------------------------------------------------------------------------------------
   set::{'jsam.parse'}
   (
   // fnc :: implements - `jsam::parse()`
   // -----------------------------------------------------------------------------------
      function($dfn, $vrs=null, $adp=null)
      {
      // validate
      // --------------------------------------------------------------------------------
         if (!is::str($dfn))
         { fail::{tpe}('frisked string extected'); }

         $vrs = (is::nul($vrs) ? obj() : $vrs);

         if (!property_exists($vrs, 'null'))
         {
            $vrs->{'null'} = null;
            $vrs->{'true'} = true;
            $vrs->{'false'} = false;
            $vrs->{'$mode'} = MODE;
            $vrs->{'$addr'} = http::get('addr');
            $vrs->{'$file'} = (!isset($vrs->{'$file'}) ? http::get('req.vrs.$file') : $vrs->{'$file'});
            $vrs->{'$path'} = (!isset($vrs->{'$path'}) ? http::get('req.vrs.$path') : $vrs->{'$path'});
         }
      // --------------------------------------------------------------------------------


      // locals
      // --------------------------------------------------------------------------------
         $cfg = jsam::get('conf.parse.literals');
         $qsb = QSB;
         $qse = QSE;
         $qsc = false;
         $rco = ['({[',']})'];

         $ctd = new object
         ([
            QSB.QSE=>(object)['ctx'=>'qot', 'dlm'=>null, 'rsl'=>''],
            '()'=>(object)['ctx'=>'exp', 'dlm'=>',+-*/=<>!?:&|%', 'rsl'=>[]],
            '{}'=>(object)['ctx'=>'obj', 'dlm'=>':,;', 'rsl'=>new object()],
            '[]'=>(object)['ctx'=>'arr', 'dlm'=>',', 'rsl'=>[]],
         ]);

         $flc = substr($dfn,0,1).substr($dfn,-1,1);

         $ctx = (isset($ctd->$flc->ctx) ? $ctd->$flc->ctx : 'str');
         $dlm = (isset($ctd->$flc->dlm) ? $ctd->$flc->dlm : '');
         $rsl = (isset($ctd->$flc->rsl) ? $ctd->$flc->rsl : '');


         $dfn = (($ctx === 'str') ? $dfn : substr($dfn, 1, -1));
         $len = strlen($dfn);

         $rec = [];
         $bfr = '';
         $obk = null;
         $fnc = null;
      // --------------------------------------------------------------------------------


      // quick result
      // --------------------------------------------------------------------------------
         if ($len < 1) { return null; }

         if ($ctx === 'qot')
         { return $dfn; }

         if ($ctx === 'str')
         {
            $dfn = parse($dfn);

            if (typeOf($dfn) === str)
            { $dfn = jsam::{'parse.subExp'}($dfn, $vrs); }

            return $dfn;
         }

         if (($ctx === 'exp') && (strlen($dfn) > 2))
         {
            $flc = substr($dfn,0,1).substr($dfn,-1,1);
            $qct = (isset($ctd->$flc->ctx) ? $ctd->$flc->ctx : 'str');
            $nmd = (($qct === 'str') ? $dfn : substr($dfn, 1, -1));

            if (($qct == 'obj') || ($qct == 'arr'))
            {
               $dfn = $nmd;
               $len = strlen($dfn);
               $ctx = $qct;
               $dlm = $ctd->$flc->dlm;
               $rsl = $ctd->$flc->rsl;
            }
            else
            {
               $sub = false;         // continue with expression ?
               $edl = strlen($dlm);
               $qsp = [strpos($nmd, $qsb), strpos($nmd, $qse)];
               $tst = $nmd;
               $rsq = false;

               if ($qsp[0] !== false)
               {
                  $rsq = true;
                  $ets = substr($nmd, $qsp[0], (($qsp[1] - $qsp[0]) +1));
                  $tst = str_replace($ets, '', $tst);
                  $tst = explode($qsb, $tst)[0];
               }

               for ($o=0; $o<$edl; $o++)
               {
                  if (strpos($tst, $dlm[$o]) !== false)
                  { $sub = true; break; }
               }

               if ($sub === false)
               {
                  if (strpos($nmd, '(') !== false)
                  { return jsam::{'parse.subExp'}($nmd, $vrs); }

                  if ($rsq === true)
                  { $nmd = str_replace([$qsb, $qse], '', $nmd); }

                  $tst = map::get($vrs, $nmd);

                  if ($tst !== null)
                  { return $tst; }

                  return $nmd;
               }
            }
         }
      // --------------------------------------------------------------------------------


      // walk & assign
      // --------------------------------------------------------------------------------
         for ($i=0; $i<$len; $i++)
         {
         // character
         // -----------------------------------------------------------------------------
            $c = $dfn[$i];
         // -----------------------------------------------------------------------------


         // "record as string" toggle
         // -----------------------------------------------------------------------------
            if ($c == $qsb) { $qsc = true; }
            if ($c == $qse) { $qsc = false; }

            if ($qsc === false)
            {
               if (strpos($rco[0], $c) !== false)
               {
                  $fi = (-1 - strpos($rco[0], $c));
                  $ec = substr($rco[1], $fi, 1);
                  $rec[] = $ec;
               }
               elseif (strpos($rco[1], $c) !== false)
               {
                  end($rec);
                  $ri = key($rec);

                  if (isset($rec[$ri]))
                  {
                     $ec = $rec[$ri];
                     if ($c == $ec){ array_pop($rec); }
                  }
               }
            }
         // -----------------------------------------------------------------------------


         // add rec chars to buffer
         // -----------------------------------------------------------------------------
            if ((count($rec) > 0) || ($qsc === true)) { $bfr .= $c;}
         // -----------------------------------------------------------------------------


         // process non-rec chars
         // -----------------------------------------------------------------------------
            if ((count($rec) < 1) && (($qsc === false)))
            {
            // add non-dlm char to buffer
            // --------------------------------------------------------------------------
               if (strpos($dlm, $c) === false) { $bfr .= $c;}
            // --------------------------------------------------------------------------

            // char is deliminator or EOI :: process buffer according to dlm & ctx
            // --------------------------------------------------------------------------
               if ((strpos($dlm, $c) !== false) || ($i == ($len -1)))
               {
               // get value & flush buffer
               // -----------------------------------------------------------------------
                  $val = $bfr;
                  $bfr = '';
               // -----------------------------------------------------------------------


               // EXPRESSION
               // -----------------------------------------------------------------------
                  if ($ctx == 'exp')
                  {
                  // add itm to sum & add opr to sum
                  // --------------------------------------------------------------------
                     $rsl[] = $val;

                     if (strpos($dlm, $c) !== false)
                     {
                        $no = (isset($dfn[$i+1]) ? $dfn[$i+1] : '');
                        $no = ((strpos($dlm, $no) !== false) ? $no : '');

                        $rsl[] = $c.$no;

                        if ($no !== '')
                        { $i += 1; continue; }
                     }
                  // --------------------------------------------------------------------

                  // end of input :: build & calculate sum
                  // --------------------------------------------------------------------
                     if ($i == ($len -1))
                     {
                     // assign sequence as result & extract first item as result
                     // -----------------------------------------------------------------
                        $seq = $rsl;
                        $rsl = trim(array_shift($seq));
                     // -----------------------------------------------------------------

                     // process single item
                     // -----------------------------------------------------------------
                        if (count($seq) < 1)
                        { return jsam::parse($rsl, $vrs); }
                     // -----------------------------------------------------------------

                     // process calculation sequence
                     // -----------------------------------------------------------------
                        if (count($seq) > 0)
                        {
                        // iterate through sequence, build sum & calculate result
                        // --------------------------------------------------------------
                           foreach ($seq as $idx => $itm)
                           {
                              $itm = trim($itm);
                              $odx = ($idx -1); if (!isset($seq[$odx])) { continue; }
                              $opr = ((strpos($dlm, $seq[$odx][0]) !== false) ? $seq[$odx] : null);

                              if ($opr !== null)
                              {
                                 $lft = $rsl;
                                 $rgt = $itm;

                                 if (is::str($lft) && (strlen($lft) > 1))
                                 {
                                    $lcs = str($lft)->get('<>');

                                    if (($lcs == '""') || ($lcs == "''") || ($lcs == "``"))
                                    { $lft = $qsb.substr($lft, 1, -1).$qse; }

                                    if ((strlen($lcs) > 1) && ($lcs[1] === ')'))
                                    {
                                       $lft = jsam::parse($lft, $vrs);

                                       if (typeOf($lft) === str)
                                       { $lft = $qsb.$lft.$qse; }
                                    }
                                 }

                                 if (is::str($rgt) && (strlen($rgt) > 1))
                                 {
                                    $rcs = str($rgt)->get('<>');

                                    if (($rcs == '""') || ($rcs == "''") || ($rcs == "``"))
                                    { $rgt = $qsb.substr($rgt, 1, -1).$qse; }

                                    if ((strlen($rcs) > 1) && ($rcs[1] === ')'))
                                    {
                                       $rgt = jsam::parse($rgt, $vrs);

                                       if (typeOf($rgt) === str)
                                       { $rgt = $qsb.$rgt.$qse; }
                                    }
                                 }

                                 $rsl = jsam::calc([$lft, $opr, $rgt], $vrs);

                                 if (typeOf($rsl) === str)
                                 { $rsl = $qsb.$rsl.$qse; }
                              }
                           }
                        // --------------------------------------------------------------

                        // return result
                        // --------------------------------------------------------------
                           if ((typeOf($rsl) === str) && ($rsl[0] == $qsb) && (substr($rsl, -1, 1) == $qse))
                           { $rsl = substr($rsl, 1, -1); }
                           return $rsl;
                        // --------------------------------------------------------------
                        }
                     // -----------------------------------------------------------------
                     }
                  // --------------------------------------------------------------------

                  // skip the rest
                  // --------------------------------------------------------------------
                     continue;
                  // --------------------------------------------------------------------
                  }
               // -----------------------------------------------------------------------

               // OBJECT
               // -----------------------------------------------------------------------
                  if ($ctx == 'obj')
                  {
                  // object key
                  // --------------------------------------------------------------------
                     if ($c == ':')
                     {
                        $flc = $val[0].substr($val, -1, 1);
                        $obk = ((($flc === '()') || ($flc === $qsb.$qse)) ? jsam::parse($val,$vrs) : $val);

                        if ($obk === null)
                        { fail::{ref}("invalid object key-name: `$val`"); }

                        //$rsl->$obk = null;
                     }
                  // --------------------------------------------------------------------

                  // object value
                  // --------------------------------------------------------------------
                     if (($c == ',') || ($c == ';') || ($i == ($len -1)))
                     {
                        if (isset($cfg->obj->keyValStr->$obk))
                        { $rsl->$obk = $val; }
                        else
                        {
                           $ext = null;

                           if (strpos($obk, '/') !== false)
                           {
                              $ext = true;

                              if (strpos($obk, '\\') === false)
                              {
                                 $tpl = 'tpl/sys/'.str_replace('/','-',$obk).'.tpl.jso';
                              }
                              else
                              {
                                 $tkp = explode('\\', str_replace(' ', '', $obk));
                                 $obk = $tkp[0];
                                 $tpl = $tkp[1];
                              }

                              if (!file_exists($tpl))
                              {
                                 $ext = null;
                              }
                              else
                              {
                                 $val = '{'.$qsb.$tpl.$qse.':'.$val.'}';
                                 $val = jsam::{'parse.extends'}(jsam::parse($val,$vrs),$vrs);
                              }
                           }

                           if ($ext === null)
                           {
                              $flc = $val[0].substr($val, -1, 1);

                              if (isset($ctd->{$flc}) || ($flc[1] === ')'))
                              { $val = jsam::parse($val,$vrs); }
                              else
                              { $val = parse($val); }
                           }

                           $rsl->$obk = $val;
                        }

                        if ($adp === true)
                        { $vrs->$obk = $rsl->$obk; }
                     }
                  // --------------------------------------------------------------------

                  // return result at end
                  // --------------------------------------------------------------------
                     if ($i == ($len -1))
                     { return $rsl; }
                  // --------------------------------------------------------------------
                  }
               // -----------------------------------------------------------------------

               // ARRAY
               // -----------------------------------------------------------------------
                  if ($ctx == 'arr')
                  {
                  // add item to result
                  // --------------------------------------------------------------------
                     $flc = $val[0].substr($val, -1, 1);
                     $tmp = jsam::parse($val,$vrs);
                     $apn = false;

                     if (typeOf($tmp) === arr)
                     {
                        $vlc = substr($val, -1, 1);

                        if ((strpos($val, 'yield') !== false) && ($vlc == ')'))
                        { $apn = true; }
                     }

                     if ($apn === true)
                     {
                        foreach ($tmp as $itm)
                        {
                           $rsl[] = $itm;
                        }
                     }
                     else
                     {
                        $rsl[] = $tmp;
                     }
                  // --------------------------------------------------------------------

                  // return result at end
                  // --------------------------------------------------------------------
                     if ($i == ($len -1)){ return $rsl; }
                  // --------------------------------------------------------------------
                  }
               // -----------------------------------------------------------------------
               }
            // --------------------------------------------------------------------------
            }
         // -----------------------------------------------------------------------------
         }
      // --------------------------------------------------------------------------------


      // return default is null
      // --------------------------------------------------------------------------------
         return null;
      // --------------------------------------------------------------------------------
      }
   );
// --------------------------------------------------------------------------------------

?>
