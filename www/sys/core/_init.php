<?

// core :: init, purposefully in closure
// --------------------------------------------------------------------------------------
   call_user_func(function()
   {
   // set cwd
   // -----------------------------------------------------------------------------------
      chdir('../../');
   // -----------------------------------------------------------------------------------


   // doc-root (CWD) absolute path
   // -----------------------------------------------------------------------------------
      define('CWD', getcwd());
   // -----------------------------------------------------------------------------------


   // core config
   // -----------------------------------------------------------------------------------
      $c = json_decode(file_get_contents(CWD.'/cfg/core/_init.json'));
   // -----------------------------------------------------------------------------------


   // time zone
   // -----------------------------------------------------------------------------------
      date_default_timezone_set($c->timeZone);
   // -----------------------------------------------------------------------------------


   // system modes
   // -----------------------------------------------------------------------------------
      define('liveMode', $c->liveMode);                              // liveMode boolean
      define('coreMode', ((liveMode === false) ? 'test' : 'live'));  // coreMode string
      define('viewMode', ((php_sapi_name()==='cli')?'cli':'gui'));   // viewMode string
   // -----------------------------------------------------------------------------------


   // require core utils
   // -----------------------------------------------------------------------------------
      $p = CWD.'/sys/core/';                                         // core path
      $h = opendir($p);                                              // dir handler

      while ($i = readdir($h))
      {
         if
         (
            is_file($p.$i) &&                                        // only files
            (strpos('._', $i[0]) === false) &&                       // not hide or auto
            (pathinfo($p.$i)['extension'] === 'php')                 // only php
         )
         { require($p.$i); }
      }
   // -----------------------------------------------------------------------------------


   // prevent double errors
   // -----------------------------------------------------------------------------------
      error_reporting(0);
   // -----------------------------------------------------------------------------------
   });
// --------------------------------------------------------------------------------------

?>