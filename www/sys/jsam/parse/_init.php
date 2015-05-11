<?

// parse frisked jsam string
// --------------------------------------------------------------------------------------
   Jsam::set('parse', function($dfn, $vrs=null, $adp=null)
   {
   // fix vars
   // -----------------------------------------------------------------------------------
      $tov = typeOf($vrs);

      if ($tov !== obj)
      { $vrs = new obj(); }

      if (!property_exists($vrs, 'null'))
      {
         $vrs->{'null'} = null;
         $vrs->{'true'} = true;
         $vrs->{'false'} = false;
         $vrs->{'$mode'} = coreMode;
      }
   // -----------------------------------------------------------------------------------


   // locals
   // -----------------------------------------------------------------------------------
      $qsb = chr(171);
      $qse = chr(187);
      $qsc = false;
      $rco = ['({[',']})'];

      $ctd = new obj
      ([
         $qsb.$qse=>(object)['ctx'=>'qot', 'dlm'=>null, 'rsl'=>''],
         '()'=>(object)['ctx'=>'exp', 'dlm'=>',+-*/=<>!?:&|%', 'rsl'=>[]],
         '{}'=>(object)['ctx'=>'obj', 'dlm'=>':,;', 'rsl'=>new obj()],
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
   // -----------------------------------------------------------------------------------


   // quick result
   // -----------------------------------------------------------------------------------
      if ($len < 1) { return null; }

      if ($ctx === 'qot')
      { return $dfn; }

      if ($ctx === 'str')
      {
         $dfn = parse::text($dfn);

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
            $sub = true;
            $edl = strlen($dlm);

            for ($o=0; $o<$dlm; $o++)
            {
               if (strpos($dfn, $opr[$o]) !== false)
               { $sub = false; break; }
            }

            if (($sub === false) && ($qct === 'str'))
            {
               $nmd = parse($nmd);

               if (typeOf($nmd) === str)
               { $nmd = jsam::{'parse.subExp'}($nmd, $vrs); }

               return $nmd;
            }
         }
      }
   // -----------------------------------------------------------------------------------


   // walk & assign
   // -----------------------------------------------------------------------------------
      for ($i=0; $i<$len; $i++)
      {
      // character
      // --------------------------------------------------------------------------------
         $c = $dfn[$i];
      // --------------------------------------------------------------------------------


      // "record as string" toggle
      // --------------------------------------------------------------------------------
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
      // --------------------------------------------------------------------------------


      // add rec chars to buffer
      // --------------------------------------------------------------------------------
         if ((count($rec) > 0) || ($qsc === true)) { $bfr .= $c;}
      // --------------------------------------------------------------------------------


      // process non-rec chars
      // --------------------------------------------------------------------------------
         if ((count($rec) < 1) && (($qsc === false)))
         {
         // add non-dlm char to buffer
         // -----------------------------------------------------------------------------
            if (strpos($dlm, $c) === false) { $bfr .= $c;}
         // -----------------------------------------------------------------------------

         // char is deliminator or EOI :: process buffer according to dlm & ctx
         // -----------------------------------------------------------------------------
            if ((strpos($dlm, $c) !== false) || ($i == ($len -1)))
            {
            // get value & flush buffer
            // --------------------------------------------------------------------------
               $val = $bfr;
               $bfr = '';
            // --------------------------------------------------------------------------


            // EXPRESSION
            // --------------------------------------------------------------------------
               if ($ctx == 'exp')
               {
               // add itm to sum & add opr to sum
               // -----------------------------------------------------------------------
                  $rsl[] = $val;

                  if (strpos($dlm, $c) !== false)
                  {
                     $no = (isset($dfn[$i+1]) ? $dfn[$i+1] : '');
                     $no = ((strpos($dlm, $no) !== false) ? $no : '');

                     $rsl[] = $c.$no;

                     if ($no !== '')
                     { $i += 1; continue; }
                  }
               // -----------------------------------------------------------------------

               // end of input :: build & calculate sum
               // -----------------------------------------------------------------------
                  if ($i == ($len -1))
                  {
                  // assign sequence as result & extract first item as result
                  // --------------------------------------------------------------------
                     $seq = $rsl;
                     $rsl = array_shift($seq);
                  // --------------------------------------------------------------------

                  // process single item
                  // --------------------------------------------------------------------
                     if (count($seq) < 1)
                     { return Jsam::parse($rsl, $vrs); }
                  // --------------------------------------------------------------------

                  // process calculation sequence
                  // --------------------------------------------------------------------
                     if (count($seq) > 0)
                     {
                     // iterate through sequence, build sum & calculate result
                     // -----------------------------------------------------------------
                        foreach ($seq as $idx => $itm)
                        {
                           $odx = ($idx -1); if (!isset($seq[$odx])) { continue; }
                           $opr = ((strpos($dlm, $seq[$odx][0]) !== false) ? $seq[$odx] : null);

                           if ($opr !== null)
                           {
                              $rsl = Jsam::calc([$rsl, $opr, $itm], $vrs);

                              if (typeOf($rsl) === str)
                              { $rsl = $qsb.$rsl.$qse; }
                           }
                        }
                     // -----------------------------------------------------------------

                     // return result
                     // -----------------------------------------------------------------
                        if ((typeOf($rsl) === str) && ($rsl[0] == $qsb) && (substr($rsl, -1, 1) == $qse))
                        { $rsl = substr($rsl, 1, -1); }
                        return $rsl;
                     // -----------------------------------------------------------------
                     }
                  // --------------------------------------------------------------------
                  }
               // -----------------------------------------------------------------------

               // skip the rest
               // -----------------------------------------------------------------------
                  continue;
               // -----------------------------------------------------------------------
               }
            // --------------------------------------------------------------------------

            // OBJECT
            // --------------------------------------------------------------------------
               if ($ctx == 'obj')
               {
               // object key
               // -----------------------------------------------------------------------
                  if ($c == ':')
                  {
                     $flc = $val[0].substr($val, -1, 1);
                     $obk = ((($flc === '()') || ($flc === $qsb.$qse)) ? Jsam::parse($val,$vrs) : $val);

                     if ($obk === null)
                     { throw new Exception('object key-name assign failed in:  '.$val); }

                     $rsl->$obk = null;
                  }
               // -----------------------------------------------------------------------

               // object value
               // -----------------------------------------------------------------------
                  if (($c == ',') || ($c == ';') || ($i == ($len -1)))
                  {
                     if (($obk === 'where') || ($obk === 'adapt') || ($obk === 'yield'))
                     { $rsl->$obk = $val; }
                     else
                     {
                        $flc = $val[0].substr($val, -1, 1);

                        if (isset($ctd->{$flc}) || ($flc[1] === ')'))
                        { $val = Jsam::parse($val,$vrs); }

                        $rsl->$obk = $val;
                     }

                     if ($adp === true)
                     { $vrs->$obk = $rsl->$obk; }
                  }
               // -----------------------------------------------------------------------

               // return result at end
               // -----------------------------------------------------------------------
                  if ($i == ($len -1))
                  {
                     return $rsl;
                  }
               // -----------------------------------------------------------------------
               }
            // --------------------------------------------------------------------------

            // ARRAY
            // --------------------------------------------------------------------------
               if ($ctx == 'arr')
               {
               // add item to result
               // -----------------------------------------------------------------------
                  $flc = $val[0].substr($val, -1, 1);
                  $tmp = Jsam::parse($val,$vrs);
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
                     { $rsl[] = $itm; }
                  }
                  else
                  { $rsl[] = $tmp; }
               // -----------------------------------------------------------------------

               // return result at end
               // -----------------------------------------------------------------------
                  if ($i == ($len -1)){ return $rsl; }
               // -----------------------------------------------------------------------
               }
            // --------------------------------------------------------------------------
            }
         // -----------------------------------------------------------------------------
         }
      // --------------------------------------------------------------------------------
      }
   // -----------------------------------------------------------------------------------


   // return default is null
   // -----------------------------------------------------------------------------------
      return null;
   // -----------------------------------------------------------------------------------
   });
// --------------------------------------------------------------------------------------



// subExp :: reference or function
// --------------------------------------------------------------------------------------
   set::{'jsam.parse.subExp'}
   (
      function($dfn, $vrs=null)
      {
         $opr = ',+-*/=<>!?:&|%';
         $opl = strlen($opr);
         $ref = true;
         $efx = false;
         $pos = strpos($dfn, '(');
         $flc = $dfn[0].substr($dfn,-1,1);

         for ($o=0; $o<$opl; $o++)
         {
            if (strpos($dfn, $opr[$o]) !== false)
            {
               $ref = false;
               break;
            }
         }

         if ($ref === true)
         {
            $omv = map::get($vrs, $dfn);
            return (($omv !== null) ? $omv : $dfn);
         }

         if (($pos !== false) && ($flc[1] === ')'))
         {
            $fnc = substr($dfn, 0, $pos);
            $arg = substr($dfn, ($pos +1), -1);
            $flc = $arg[0].substr($arg,-1,1);
//            $arg = jsam::parse($arg, $vrs);

            return run::{'jsam.parse.'.$fnc}($arg, $vrs);
         }

         return $dfn;
      }
   );
// --------------------------------------------------------------------------------------



// sepExpSec :: separate expression sequences
// --------------------------------------------------------------------------------------
   set::{'jsam.parse.sepExpSeq'}
   (
      function($str)
      {
         $qsc = [chr(171), chr(187)];
         $dol = ['==', '!=', '<=', '>=', '&&', '||'];
         $sol = ['=', '!', '<', '>', '&', '|'];

         $flc = str::in($str)->get('<>');

         if ($flc === '[]')
         { $str = str::in($str)->get('><'); }

         $lst = explode(',', $str);
         $rsl = [];

         foreach ($lst as $exp)
         {
            $flc = str::in($str)->get('<>');

            if ($flc !== '()')
            { throw new Exception('expression expected'); }

            $exp = str::in($exp)->get('><');
            $seq = null;
            $opr = null;

            foreach ($dol as $do)
            {
               if (strpos($exp, $do) !== false)
               { $seq = explode($do, $exp); $opr = $do; break; }
            }

            if ($seq === null)
            {
               foreach ($sol as $so)
               {
                  if (strpos($exp, $so) !== false)
                  { $seq = explode($so, $exp); $opr = $so; break; }
               }
            }

            if ($opr === null)
            { throw new Exception('unrecognized operator in expression'); }

            $rsl[] = [$seq[0], $opr, $seq[1]];
         }

         return $rsl;
      }
   );
// --------------------------------------------------------------------------------------

?>
