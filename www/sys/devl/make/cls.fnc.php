<?

// def :: make.cls - extends: `devl`
// --------------------------------------------------------------------------------------
   set::{'devl.make.cls'}
   (
   // fnc :: cls - implements: `devl::make.cls()`
   // -----------------------------------------------------------------------------------
      function($src,$inf)
      {
      // rsl :: return - updated `$src`
      // --------------------------------------------------------------------------------
         return str($src)->swop('template', $inf->make->cls);
      // --------------------------------------------------------------------------------
      }
   // -----------------------------------------------------------------------------------
   );
// --------------------------------------------------------------------------------------

?>
