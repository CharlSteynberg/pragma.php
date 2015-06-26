<?

// def :: prep - extends: `jsam`
// --------------------------------------------------------------------------------------
   set::{'jsam.prep'}
   (
   // fnc :: prep - implements: `jsam::prep()`
   // -----------------------------------------------------------------------------------
      function($jd, $fn=null)
      {
      // validate
      // --------------------------------------------------------------------------------
         if (!is::str($jd) || (typeOf($jd,str) !== EXP))
         { fail::{Tpe}('expression string expected;  i.e: ({foo:123})'); }

         if (strlen(trim($jd)) < 5)
         { fail::{Src}('string is too short'); }

         if ($fn === null)
         { $fn = '[string]'; }
      // --------------------------------------------------------------------------------


      // constants (never changes during runtime)
      // --------------------------------------------------------------------------------
         $sx = ((MODE !== 'live') ? jsam::get('conf.prep') : null);

         $qb = QSB;                                      // quote bgn
         $qe = QSE;                                      // quote end
         $ds = strlen($jd);                              // document size
         $mi = ($ds-1);                                  // maximum index
         $st = array('"'=>1, "'"=>1, '`'=>1);            // string tokens  (each toggle)
         $ct = array('//'=>"\n", '/*'=>'*/');            // comment tokens (begin & end)
         $ws = "\r \n \t";                               // white space
      // --------------------------------------------------------------------------------


      // variables (changes during runtime)
      // --------------------------------------------------------------------------------
         $lc = array(1,0);                               // curent Line and Column

         $cn = 'doc';                                    // context name
         $oc = $cn;                                      // old context
         $ca = array($cn);                               // context array
         $cl = 0;                                        // context array
         $cb = null;                                     // context boolean

         $dc = '';                                       // double characters
         $cr = 'ds';                                     // current reference
         $pr = $cr;                                      // previous reference

         $sc = false;                                    // string context (quoted)
         $vc = false;                                    // void context   (commented)
         $rs = '';                                       // result
      // --------------------------------------------------------------------------------


      // syntax checking vars only
      // --------------------------------------------------------------------------------
         if ($sx !== null)
         {
            $co = $sx->cat;                              // context operators
            $no = null;                                  // new operator
            $oo = null;                                  // old operator
            $ot = $sx->tkn;                              // operator tokens
            $rd = $sx->dsc;                              // reference description
            $mx = $sx->crm;                              // context matrix
            $rp = "$cn.$pr.$cr";                         // reference path

            $jd .= ' ';
            $ds += 1;
            $mi += 1;
         }
      // --------------------------------------------------------------------------------


      // walk, check errors, minify
      // --------------------------------------------------------------------------------
         for ($i=0; $i<$ds; $i++)
         {
         // character variables
         // -----------------------------------------------------------------------------
            $pc = ($i>0 ? $jd[$i-1] : null);             // previous character
            $cc = $jd[$i];                               // current character
            $nc = ($i<$mi ? $jd[$i+1] : null);           // next character
            $dc = (($nc !== null) ? ($cc.$nc) : null);   // double chars
         // -----------------------------------------------------------------------------


         // line & column count
         // -----------------------------------------------------------------------------
            if ($cc == "\n") {$lc[0]++; $lc[1]=0;} else {$lc[1]++;}
         // -----------------------------------------------------------------------------


         // void context (comment) toggle
         // -----------------------------------------------------------------------------
            if ($sc === false)
            {
               if ($vc === false)
               {
                  if (($dc !== null) && isset($ct[$dc]))
                  { $vc = $ct[$dc]; }
                  elseif (($pc.$cc) == '*/')
                  { continue; }
               }
               else
               {
                  if ($cc === $vc)
                  { $vc = false; }
                  elseif (($dc !== null) && ($dc === $vc))
                  {
                     $vc = false;
                     continue;
                  }
               }
            }
         // -----------------------------------------------------------------------------


         // skip the rest if current char is commented
         // -----------------------------------------------------------------------------
            if ($vc !== false){ continue; }
         // -----------------------------------------------------------------------------


         // quoted string context toggle
         // -----------------------------------------------------------------------------
            if (isset($st[$cc]))
            {
               if ($sc === false)
               { $sc = $cc; $cc=$qb; }
               else if ($sc === $cc)
               {
                  if ($pc !== '\\')
                  { $sc = false; $cc=$qe; }
                  else
                  {
                     if ($jd[$i-2] === '\\')
                     { $sc = false; $cc=$qe; }
                  }
               }
            }
            else
            {
               if ($sc !== false)
               {
                  if (($sc !== '`') && ($cc === "\n"))
                  { $sc = false; }

                  if (($cc === '\\') && ($nc !== '\\'))
                  { continue; }
               }
            }
         // -----------------------------------------------------------------------------


         // syntax checking
         // -----------------------------------------------------------------------------
            if ($sx !== null)
            {
            // define context references
            // --------------------------------------------------------------------------
               $pr = (($cr != 'ws') ? $cr : $pr);        // previous reference
               $cr = null;                               // current reference (reset)

               if (($sc !== false) || ($cc == $qe)) { $cr = 'qs'; }
               elseif (strpos($ws, $cc) !== false) { $cr = 'ws'; }
               elseif(isset($ot->$cc))
               {
                  $cr = $ot->$cc;
                  $oo = $no;
                  $no = $cr;
               }
               elseif(is_numeric($cc)) { $cr = 'dn'; }
               else{ $cr = 'pt'; }

               if ($cn == 'obj')
               {
                  if (((strpos('os ld cd', $no) !== false) || (strpos('os kn ld cd', $pr) !== false)) && (strpos('pt qs dn ft so mo', $cr) !== false))
                  { $cr = 'kn'; }
                  elseif ((strpos('dn pt', $pr) !== false) && (strpos('pt mm so', $cr) !== false))
                  { $cr = 'pt'; }

                  if ((strpos('os ld cd', $oo) !== false) && ($cr == 'so'))
                  { $cr = 'kn'; }

                  if ((!isset($co->$cc)) && (strpos('kn rd ld cd qs ws', $cr) === false))
                  { $cr = 'pt'; }
               }

               if ($i == $mi) {$cr = 'de';}              // document end
            // --------------------------------------------------------------------------

            // define current context name and level
            // --------------------------------------------------------------------------
               if (($sc === false) && ($cc !== $qe) && isset($co->$cc))
               {
                  $cb = $co->{$cc}[1];

                  if ($cb > 0)
                  { $ca[] = $co->{$cc}[0]; }             // ctx level up
                  elseif ($cn == $co->{$cc}[0])
                  { $oc = array_pop($ca); }              // ctx level down

                  end($ca);

                  $cl = key($ca);                        // context level
                  $oc = $cn;                             // old context name
                  $cn = $ca[$cl];                        // context name
                  $cb = null;                            // reset boolean
               }
            // --------------------------------------------------------------------------

            // validate context references if not string
            // --------------------------------------------------------------------------
               if ($sc === false)
               {
                  $er = 0;
                  $tc = $cn;

                  if (isset($co->$cc))
                  {
                     $to = $co->$cc;

                     if (($cn == $to[0]) && ($to[1] > 0))
                     { $tc = $oc; }

                     if (($oc == $to[0]) && ($to[1] < 1))
                     { $tc = $oc; }
                  }

                  $rp = "$tc.$pr.$cr";                           // reference path

                  if ($mx->$tc->$pr->$cr < 1) {$er++;}           // if ref rule is 0
                  if (($cr == 'de') && ($cn != 'doc')) {$er++;}  // if brace mismatch

                  if ($er > 0)
                  {
                     core::stack
                     ([
                        'file'=>$fn,
                        'line'=>"$lc[0]:$lc[1]",
                        'call'=>'fail::syntax',
                        'args'=>explode('.',$rp)
                     ]);

                     fail::{'syntax'}('unexpected `'.$rd->$cr.'`');
                  }
               }
            // --------------------------------------------------------------------------
            }
         // -----------------------------------------------------------------------------


         // skip if whitespace
         // -----------------------------------------------------------------------------
            if (($sc === false) && (strpos($ws, $cc) !== false))
            { continue; }
         // -----------------------------------------------------------------------------


         // fix string
         // -----------------------------------------------------------------------------
            if ($sc === false)
            {
               if (strpos(')}]{', $cc) !== false)
               {
                  $xz = substr($rs, -1, 1);

                  if ($cc == '{')
                  {
                     if ($xz.$cc == '}{')
                     { $rs .= ','; }
                  }
                  elseif (($xz == ',') || ($xz == ';'))
                  { $rs = substr($rs, 0, -1); }
               }
            }
         // -----------------------------------------------------------------------------


         // add to result
         // -----------------------------------------------------------------------------
            $rs .= $cc;
         // -----------------------------------------------------------------------------
         }
      // --------------------------------------------------------------------------------


      // return result
      // --------------------------------------------------------------------------------
         if (substr($rs, -1, 1) === ';'){ $rs = substr($rs, 0, -1); }
         return $rs;
      // --------------------------------------------------------------------------------
      }
   // -----------------------------------------------------------------------------------
   );
// --------------------------------------------------------------------------------------

?>
