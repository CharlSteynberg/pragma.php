<?

   set::{"jsam.parse.with"}
   (
      function($dfn, $vrs)
      {
      // validate
      // --------------------------------------------------------------------------------
         $dfn = jsam::parse($dfn, $vrs);

         if (!is::obj($dfn))
         { throw new Exception('with: object expected'); }

         if (!isset($dfn->yield))
         { throw new Exception('with: yield is mandatory'); }

         if (!isset($dfn->basis))
         {
            $dfn->basis = new obj();
            $omt = ['null', 'true', 'false'];

            foreach ($vrs as $k => $v)
            {
               if (!in_array($k, $omt))
               { $dfn->basis->$k = $v; }
            }
         }
      // --------------------------------------------------------------------------------


      // locals
      // --------------------------------------------------------------------------------
         $bse = $dfn->basis;
         $tpe = typeOf($bse);
         $dat = null;
         $rsl = [];
      // --------------------------------------------------------------------------------


      // basis
      // --------------------------------------------------------------------------------
         if (($tpe === str) && (strpos($bse, '/') !== false))
         {
            if ($bse[0] === '/')
            { $bse = 'pub'.$bse; }

            $inf = path::info($bse);

            if ($inf->stat !== 200)
            { throw new Exception('"'.$bse.'" is '.server::get('conf')->statCode->{$inf->stat}); }
         }

         if (($tpe === str) && (str::typeOf($bse) === str))
         {
         // data :: from database
         // -----------------------------------------------------------------------------
            $dat = dbase::query($dfn);
         // -----------------------------------------------------------------------------
         }
         else
         {
         // data :: file
         // -----------------------------------------------------------------------------
            if (($tpe === arr) || ($tpe === obj))
            { $dat = $bse; }
            elseif (($tpe === str) && (str::typeOf($bse) === pth))
            {
               if ($inf->type === 'file')
               {
                  $dat = parse::file($bse);

                  if (($inf->extn === 'jsam') && (typeOf($dat) === obj) && (strpos(key($dat), '/') !== false))
                  { $dat = $dat->{key($dat)}; }
               }
               else
               { $dat = path::read($bse); }
            }
         // -----------------------------------------------------------------------------


         // data :: object
         // -----------------------------------------------------------------------------
            if (typeOf($dat) === obj)
            {
               $num = 0;
               $tmp = [];

               foreach ($dat as $k => $v)
               {
                  $num++;
                  $tmp[] = new obj(['id'=>$num, 'key'=>$k, 'value'=>$v]);
               }

               $dat = $tmp;
            }
         // -----------------------------------------------------------------------------


         // data must be array
         // -----------------------------------------------------------------------------
            if (!is::arr($dat))
            {
               throw new Exception('array expected');
            }
         // -----------------------------------------------------------------------------


         // data :: array
         // -----------------------------------------------------------------------------
            if (typeOf($dat[0]) !== obj)
            {
               $tmp = [];

               foreach ($dat as $k => $v)
               { $tmp[] = new obj(['id'=>($k +1), 'key'=>($k +1), 'value'=>$v]); }

               $dat = $tmp;
            }
         // -----------------------------------------------------------------------------


         // fetch :: array
         // -----------------------------------------------------------------------------
            if (isset($dfn->fetch))
            {
               $tmp = [];

               foreach ($dat as $row)
               {
                  $itm = new obj();

                  foreach ($dfn->fetch as $ftc => $nme)
                  {
                     $mv = map::get($row, $ftc);

                     if ($mv !== null)
                     { $itm->$nme = $mv; }
                  }

                  if (length($itm) > 0)
                  { $tmp[] = $itm; }
               }

               $dat = $tmp;
            }
         // -----------------------------------------------------------------------------


         // where :: array
         // -----------------------------------------------------------------------------
            if (isset($dfn->where))
            {
               $whr = jsam::{'parse.sepExpSeq'}($dfn->where, $vrs);
               $tmp = [];

               foreach ($dat as $row)
               {
                  foreach ($row as $cn => $cv)
                  { $vrs->$cn = $cv; }

                  $apn = true;

                  foreach ($whr as $sum)
                  {
                     if (jsam::calc($sum, $vrs) === false)
                     { $apn = false; break; }
                  }

                  if ($apn === true)
                  {
                     reset($row);
                     $tmp[] = $row;
                  }
               }

               $dat = $tmp;
            }
         // -----------------------------------------------------------------------------


         // if no data, return null
         // -----------------------------------------------------------------------------
            if (length($dat) < 1)
            {
               return null;
            }
         // -----------------------------------------------------------------------------


         // group :: array
         // -----------------------------------------------------------------------------
            if (isset($dfn->group))
            {
               throw new Exception('with - "group" option is not developed yet!!');
            }
         // -----------------------------------------------------------------------------



         // order :: array
         // -----------------------------------------------------------------------------
            if (isset($dfn->order))
            {
               $aol = ['asc', 'dsc', 'rnd'];
               $ord = $dfn->order;
               $tpe = typeOf($ord);

               if ($tpe === str)
               { $ord = new obj([key($dat[0])=>$ord]); }

               reset($ord);

               $col = key($ord);
               $opt = strtolower($ord->$col);
               $ord = [];
               $tmp = [];

               foreach ($dat as $row)
               { $ord[] = $row->$col; }

               switch ($opt)
               {
                  case 'asc' : sort($ord); break;
                  case 'dsc' : rsort($ord); break;
                  case 'rnd' : shuffle($ord); break;
               }

               foreach ($ord as $itm)
               {
                  foreach ($dat as $row)
                  {
                     $val = map::get($row, $col);

                     if ($val === $itm)
                     { $tmp[] = $row; }
                  }
               }

               $dat = $tmp;
            }
         // -----------------------------------------------------------------------------



         // limit :: array
         // -----------------------------------------------------------------------------
            if (!isset($dfn->limit))
            { $dfn->limit = dbase::get('conf.noLimit'); }

            $lmt = $dfn->limit;
            $tmp = [];

            foreach ($dat as $idx => $row)
            {
               if ($idx === $lmt)
               { break; }

               $tmp[] = $row;
            }

            $dat = $tmp;
         // -----------------------------------------------------------------------------
         }
      // --------------------------------------------------------------------------------



      // adapt
      // --------------------------------------------------------------------------------
         $adp = (isset($dfn->adapt) ? $dfn->adapt : null);
      // --------------------------------------------------------------------------------



      // yield
      // --------------------------------------------------------------------------------
         foreach ($dat as $row)
         {
         // variables :: column names
         // -----------------------------------------------------------------------------
            foreach ($row as $cn => $cv)
            { $vrs->$cn = $cv; }
         // -----------------------------------------------------------------------------

         // adapt :: variable names
         // -----------------------------------------------------------------------------
            if ($adp !== null)
            {
               $avn = jsam::parse($adp, $vrs, true);

               foreach ($avn as $an => $av)
               { $vrs->$an = $av; }
            }
         // -----------------------------------------------------------------------------

         // add parsed yield to result
         // -----------------------------------------------------------------------------
            $yld = jsam::parse($dfn->yield, $vrs);

            if ($yld !== null)
            { $rsl[] = $yld; }
         // -----------------------------------------------------------------------------
         }
      // --------------------------------------------------------------------------------



      // return result
      // --------------------------------------------------------------------------------
         return $rsl;
      // --------------------------------------------------------------------------------
      }
   );

?>
