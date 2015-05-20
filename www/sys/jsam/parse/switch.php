<?

   set::{"jsam.parse.switch"}
   (
      function($dfn, $vrs)
      {
      // locals
      // --------------------------------------------------------------------------------
         $rsl = null;
      // --------------------------------------------------------------------------------


      // validate
      // --------------------------------------------------------------------------------
         $dfn = jsam::parse($dfn, $vrs);

         if (is::obj($dfn))
         { $dfn = [$dfn]; }

         if (!is::arr($dfn))
         { throw new Exception('switch: array expected'); }

         if (!isset($dfn[0]->where) || !isset($dfn[0]->yield))
         { throw new Exception('switch: where & yield is mandatory'); }
      // --------------------------------------------------------------------------------


      // loop through options
      // --------------------------------------------------------------------------------
         foreach ($dfn as $opt)
         {
            $whr = jsam::parse($opt->where, $vrs);

            if ($whr === true)
            {
               if (isset($opt->adapt))
               {
                  $avn = jsam::parse($opt->adapt, $vrs, true);

                  foreach ($avn as $an => $av)
                  { $vrs->$an = $av; }
               }

               $rsl = jsam::parse($opt->yield, $vrs);
               break;
            }
         }
      // --------------------------------------------------------------------------------


      // return result
      // --------------------------------------------------------------------------------
         return $rsl;
      // --------------------------------------------------------------------------------
      }
   );

?>
