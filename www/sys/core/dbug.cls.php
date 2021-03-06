<?

// test :: validation
// --------------------------------------------------------------------------------------
   class dbug
   {
   // type
   // -----------------------------------------------------------------------------------
      public static function type($gvn, $exp)
      {
         $gvn = (!is::type($gvn) ? typeOf($gvn) : $gvn);

         if ($gvn !== $exp)
         {
            fail::{'type mismatch'}("$exp expected, but $gvn was given");
         }

         return true;
      }
   // -----------------------------------------------------------------------------------


   // vain
   // -----------------------------------------------------------------------------------
      public static function vain($dfn)
      {
         if (span($gvn) < 1)
         { fail::{'exception'}("non-empty value expected"); }

         return true;
      }
   // -----------------------------------------------------------------------------------


   // path
   // -----------------------------------------------------------------------------------
      public static function path($pth)
      {
         $rpn = path::norm($pth);
         $apn = CWD.$rpn;

         if (!is_readable($apn) || !file_exists($apn))
         {
            $wrd = (file_exists($apn) ? 'forbidden' : 'undefined');
            $wrd = (!is_file($apn) ? 'not a file' : $wrd);

            fail::{'file'}("`$rpn` is `$wrd`");
         }

         return $rpn;
      }
   // -----------------------------------------------------------------------------------


   // file
   // -----------------------------------------------------------------------------------
      public static function file($pth)
      {
         $rpn = self::path($pth);
         $apn = CWD.$rpn;

         if (!is_file($apn))
         { fail::{'file'}("`$rpn` is not a file"); }

         return $rpn;
      }
   // -----------------------------------------------------------------------------------


   // fnc :: call - statically (if function name is not pre-defined)
   // -----------------------------------------------------------------------------------
      public static function __callStatic($ref, $arg)
      {
      // cnd :: stack - if not stacked already
      // --------------------------------------------------------------------------------
         if (!str(core::get('stack')[0]->call)->has(['call::debug','call::dbug']))
         { core::stack(); }
      // --------------------------------------------------------------------------------

      // def :: vars - locals
      // --------------------------------------------------------------------------------
         $lai = (count($arg) -1);
         $arg = ((($lai > -1) && ($arg[$lai] === SDL)) ? $arg[($lai - 1)] : $arg);
         $lne = "<span>--------------------------------------------------</span>";
         $msg = '';
         $out = "<br>";

         $pts = explode(': ', $ref);

         if (count($pts) > 1)
         {
            $ref = $pts[0];
            $msg = $pts[1]." $msg";
         }
      // --------------------------------------------------------------------------------

      // run :: loop - on `$arg`
      // --------------------------------------------------------------------------------
         foreach ($arg as $key => $val)
         {
            $out .= "arg[$key]&nbsp;&nbsp;".typeOf($val).'&nbsp;&nbsp;<span>('.span($val).')</span><br>';
            $out .= $lne;
            $out .= '<pre>'.to::str($val).'</pre>';
            $out .= $lne;
            $out .= "<br><br>";
         }
      // --------------------------------------------------------------------------------

      // run :: fail - with debug output
      // --------------------------------------------------------------------------------
         fail::{"debug $ref"}($msg,$out);
      // --------------------------------------------------------------------------------
      }
   // -----------------------------------------------------------------------------------
   }
// --------------------------------------------------------------------------------------



// fnc :: dbug - quick arguments print and fail with stack-trace
// --------------------------------------------------------------------------------------
   function dbug()
   {
      core::stack();
      dbug::{'test'}(func_get_args(), SDL);
   }
// --------------------------------------------------------------------------------------


// fnc :: dbug - alias
// --------------------------------------------------------------------------------------
   function debug()
   {
      core::stack();
      dbug::{'test'}(func_get_args(), SDL);
   }
// --------------------------------------------------------------------------------------

?>
