<?

   set::{"jsam.parse.random"}
   (
      function($dfn, $vrs)
      {
         $dfn = parse::text($dfn,$vrs);
         $tpe = typeOf($dfn);

         if (($tpe === str) && (strpos($dfn, ',') !== false))
         {
            $pts = explode(',', $dfn);
            $min = ($pts[0] -0);
            $max = ($pts[1] -0);
            $rsl = rand($min, $max);

            return $rsl;
         }

         if ($tpe === arr)
         {
            $min = 0;
            $max = (count($dfn) -1);
            $key = rand($min, $max);
            $rsl = $dfn[$key];

            return $rsl;
         }

         return null;
      }
   );

?>
