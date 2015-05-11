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

      return call_user_func_array($d, to::arr($a));
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
