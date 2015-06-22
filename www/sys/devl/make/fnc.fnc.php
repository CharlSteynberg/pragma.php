<?

// def :: make.fnc - extends: `devl`
// --------------------------------------------------------------------------------------
   set::{'devl.make.fnc'}
   (
   // fnc :: fnc - implements: `devl::make.fnc()`
   // -----------------------------------------------------------------------------------
      function($src,$inf)
      {
      // def :: local - vars
      // --------------------------------------------------------------------------------
         $fnc = explode('.', $inf->make->ref);
         $fnc = array_pop($fnc);
         $cls = $inf->make->cls;
         $ref = $inf->make->ref;
      // --------------------------------------------------------------------------------

      // rsl :: write - updated `$src`
      // --------------------------------------------------------------------------------
         $src = str($src)->swop
         (
            ['tplRef','parent','tplMap','tplFnc'],
            [$ref, $cls, "$cls.$ref", $fnc]
         );

         path::make($inf->path,$src);
      // --------------------------------------------------------------------------------
      }
   // -----------------------------------------------------------------------------------
   );
// --------------------------------------------------------------------------------------

?>
