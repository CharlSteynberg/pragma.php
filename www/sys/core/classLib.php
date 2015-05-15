<?


// logging
// --------------------------------------------------------------------------------------
   class log
   {
      public static function __callStatic($k,$a)
      {
         $p = explode('/', __DIR__); array_pop($p); array_pop($p);
         $p = implode($p, '/');
         $p = "$p/log/$k.log";
         $d = date('Y-m-d').' '.date('h:i:s').' '.$a[0]."\n";

         if (!is_readable($p))
         {
            $m = (!file_exists($p) ? 'undefined' : 'forbidden');
            throw new Exception('"'.$p.'" is '.$m);
         }

         file_put_contents($p, $d, FILE_APPEND);
      }
   }
// --------------------------------------------------------------------------------------




// map :: set/get object/array tree by string.path.map
// --------------------------------------------------------------------------------------
   class map
   {
   // fix :: map path
   // -----------------------------------------------------------------------------------
      private static function fix($o, $p)
      {
         $p = explode('.', $p);
         $r = array_shift($p);

         foreach($p as $i)
         {
            $r = ((isset($o->$r) && (typeOf($o->$r) === fnc)) ? $r.'___'.$i : ($r.'.'.$i));
         }

         return $r;
      }
   // -----------------------------------------------------------------------------------


   // set :: object path value
   // -----------------------------------------------------------------------------------
      public static function set(&$o, $p, $v=null)
      {
         if ((typeOf($o) !== obj) || (typeOf($p) !== str))
         { throw new Exception('(obj,str,val) sequence expected'); }

         $p = explode('.', self::fix($o, $p));
         $x =& $o;

         foreach ($p as $s)
         {
            if (!isset($x->$s)){ $x->$s = new obj(); }
            $x =& $x->$s;
         }

         $x = $v;

         return $o;
      }
   // -----------------------------------------------------------------------------------


   // get :: object/array path value
   // -----------------------------------------------------------------------------------
      public static function get($o, $m, $f=null, $b=null)
      {
      // validate
      // --------------------------------------------------------------------------------
         if ((typeOf($o) !== obj) || (typeOf($m) !== str))
         { throw new Exception('(obj,str) type sequence expected'); }

         if (($f !== null) && (typeOf($f) !== fnc))
         { throw new Exception('function expected'); }
      // --------------------------------------------------------------------------------

      // locals
      // --------------------------------------------------------------------------------
         $p = explode('.', self::fix($o, $m));
         $c =& $o;
      // --------------------------------------------------------------------------------

      // loop & assign
      // --------------------------------------------------------------------------------
         foreach ($p as $s)
         {
            $t = typeOf($c);

            if (($t === fnc) || (strlen($s) < 1))
            { return null; }

            if ($s[0] === '-')
            {
               $l = (count($c) -1);
               $s = ($l - parse::text($s[1]));
            }

            if (($t === obj) && isset($c->$s)){ $c = $c->$s; }
            elseif (($t === arr) && isset($c[$s])){ $c = $c[$s]; }
            else
            { return null; }
         }
      // --------------------------------------------------------------------------------

      // find is null, return result
      // --------------------------------------------------------------------------------
         if ($f === null) { return $c; }
      // --------------------------------------------------------------------------------

      // search vars
      // --------------------------------------------------------------------------------
         $t = typeOf($c);
         $r = null;
         $b = ((typeOf($b) === arr) ? obj($b) : $b);
      // --------------------------------------------------------------------------------

      // sorting
      // --------------------------------------------------------------------------------
         if ($b !== null)
         {
            if (isset($b->order))
            {
               $c = arr($c);
               $z = $b->order;

               if ($z === 'asc')
               { ksort($c); }
               elseif (($z === 'dsc') || ($z === 'desc'))
               { krsort($c); }
            }
         }
      // --------------------------------------------------------------------------------

      // loop & assign
      // --------------------------------------------------------------------------------
         if (($t === obj) || ($t === arr))
         {
            $r = [];

            foreach ($c as $i => $v)
            {
               $t = call_user_func_array($f, [$i,$v]);

               if ($t !== null){ $r[] = $t; }

               if ($b !== null)
               {
                  if (isset($b->count) && (count($r) === $b->count)){ break; }
                  if (isset($b->limit) && (count($r) === $b->limit)){ break; }
                  if (property_exists($b,'value') && ($t === $b->value)){ break; }
               }
            }

            if (count($r) < 1){ $r = null; }
//            if (count($r) === 1){ $r = $r[0]; }
         }
         else
         { $r = call_user_func_array($f, $c); }
      // --------------------------------------------------------------------------------

      // result
      // --------------------------------------------------------------------------------
         return $r;
      // --------------------------------------------------------------------------------
      }
   // -----------------------------------------------------------------------------------

   // return prepared map & args
   // -----------------------------------------------------------------------------------
      public static function sep($o, $k, $a)
      {
         $k = explode('.', $k);
         $c = array_shift($k);
         $m = implode($k, '.');
         $a = to::arr($a);

         if (($o === 'set') || ($o === 'get'))
         { return [$c, $m, (isset($a[0]) ? $a[0] : null), (isset($a[1]) ? $a[1] : null)]; }

         if ($o === 'run')
         {
            return [$c, $m];
         }
      }
   // -----------------------------------------------------------------------------------
   }
// --------------------------------------------------------------------------------------



// set::app.mod.etc
// --------------------------------------------------------------------------------------
   class set
   {
   // class called statically
   // -----------------------------------------------------------------------------------
      public static function __callStatic($k, $a)
      {
         $p = map::sep('set', $k, $a);

         if (typeOf($p[2]) === arr)
         {
            $o = false;

            foreach($p[2] as $i => $v)
            {
               if (!is_int($i))
               { $o = true; break; }
            }

            if ($o === true)
            { $p[2] = new obj($p[2]); }
         }

         return $p[0]::set($p[1], $p[2], $p[3]);
      }
   // -----------------------------------------------------------------------------------
   }
// --------------------------------------------------------------------------------------



// set::app.mod.etc
// --------------------------------------------------------------------------------------
   class get
   {
   // class called statically
   // -----------------------------------------------------------------------------------
      public static function __callStatic($k, $a)
      {
         $p = map::sep('get', $k, $a);
         return $p[0]::get($p[1], $p[2], $p[3]);
      }
   // -----------------------------------------------------------------------------------
   }
// --------------------------------------------------------------------------------------



// set::app.mod.etc
// --------------------------------------------------------------------------------------
   class run
   {
   // class called statically
   // -----------------------------------------------------------------------------------
      public static function __callStatic($k, $a)
      {
         $p = map::sep('get', $k, $a);
         return $p[0]::{$p[1]}($a, '_{arg:0}_');
      }
   // -----------------------------------------------------------------------------------
   }
// --------------------------------------------------------------------------------------



// parse :: convert text to literal value
// --------------------------------------------------------------------------------------
   class parse
   {
   // text :: convert text to value
   // -----------------------------------------------------------------------------------
      public static function text($d,$a=null)
      {
         if (typeOf($d) === str)
         {
            if ($d === 'null'){ return null; }
            if ($d === 'true'){ return true; }
            if ($d === 'false'){ return false; }

            if (is_numeric($d) && preg_match('/^[0-9-\.]+$/', $d))
            { return ($d - 0); }

            if (strlen($d) > 2)
            {
               $r = trim($d);

               $flc = substr($r,0,1).substr($r,-1,1);
               $qfl = chr(171).chr(187);

               if ($flc === $qfl)
               {
                  if ((strpos($r, '(') !== false) && (strpos($r, ')') !== false))
                  {
                     $ref = explode(')', explode('(', $r)[1])[0];
                     $val = map::get($a, $ref);
                     $tpe = typeOf($val);

                     if ($val !== null)
                     { return str_replace('('.$ref.')', $val, $r); }
                  }
               }

               if ($flc === '()')
               { return jsam::parse(jsam::frisk($d), $a); }

               if (($flc === '{}') || ($flc === '[]'))
               {
                  if (strpos($d, $qfl[0]) !== false)
                  {
                     $d = str_replace('"', '\"', $d);
                     $d = str_replace($qfl[0], '"', $d);
                     $d = str_replace($qfl[1], '"', $d);
                  }

                  $d = json_decode($d);

                  if (($d === null) && (strlen($r) > 2))
                  { fail::syntax('json parse failed', '[string]', 0); }

                  return $d;
               }
            }
         }

         return $d;
      }
   // -----------------------------------------------------------------------------------


   // file :: parse json or jsam files
   // -----------------------------------------------------------------------------------
      public static function file($d, $a=null)
      {
         $p = CWD.'/'.$d;

         if (!is_readable($p))
         {
            $m = (!file_exists($p) ? 'undefined' : 'forbidden');
            throw new Exception('"'.$d.'" is '.$m);
         }

         if (!is_file($p))
         { throw new Exception('"'.$d.'" is not a file'); }

         $x = substr($d, -4, 4);
         $t = ['json', 'jsam'];

         if (!in_array($x, $t))
         { throw new Exception('parsing type "'.$x.'" is not supported'); }

         if ($x === 'json')
         {
            $c = file_get_contents($p);
            $r = json_decode($c);

            if (($r === null) && (strlen(trim($c)) > 0))
            { fail::syntax('json parse failed', $d, 0); }

            return $r;
         }

         if ($x === 'jsam')
         { return jsam::parse(jsam::frisk($d), $a); }
      }
   // -----------------------------------------------------------------------------------
   }
// --------------------------------------------------------------------------------------



// frisk :: strings
// --------------------------------------------------------------------------------------
   class frisk
   {
      public static function path($s)
      {
         $s = ((length($s) > 1) ? trim($s, '/') : $s);
         $a = 'abcdefghijklmnopqrstuvwxyz';
         $c = '0123456789'.$a.strtoupper($a).'-_/.';
         $l = strlen($s);
         $r = '';

         for ($i=0; $i<$l; $i++)
         {
            if (strpos($c, $s[$i]) !== false)
            { $r .= $s[$i]; }
         }

         return $r;
      }

      public static function input($s)
      {
         $s = str($s);
         $flc = substr($s, 0, 1).substr($s, -1, 1);

         if ($flc == '()')
         { return ''; }
      }
   };
// --------------------------------------------------------------------------------------


?>
