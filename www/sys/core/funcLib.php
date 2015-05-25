<?

// call :: enhanced "call_user_func_array"
// --------------------------------------------------------------------------------------
   function call($d, $a=null)
   {
      $dt = typeOf($d);
      $at = typeOf($a);

      if (($dt !== str) && ($dt !== fnc))
      { return null; }

      if (($dt === str) && !function_exists($d))
      { return null; }

      if ($at !== arr)
      {
         if ($at === obj)
         { $a = to::arr($a); }
         else
         { $a = [$a]; }
      }

      return call_user_func_array($d, $a);
   }
// --------------------------------------------------------------------------------------



// length :: count "length" logically: nul, bln, str, num, arr, obj
// --------------------------------------------------------------------------------------
   function length($d)
   {
      $t = typeOf($d);

      if ($t === nul) { return 0; }
      if ($t === int) { return strlen(to::str($d)); }
      if ($t === str) { return strlen($d); }
      if ($t === bln) { return (($d === true) ? 1 : 0); }
      if ($t === arr) { return count($d); }

      if ($t === flt)
      {
         $d = to::str($d);
         $p = explode('.', $d);

         if (count($p) < 2)
         { return strlen($d); }

         return strlen($p[1]);
      }

      if ($t === obj)
      {
         $n = count($d);

         if (($n === 1) && (key($d) === null))
         { $n = 0; }

         return $n;
      }
   }
// --------------------------------------------------------------------------------------


?>
