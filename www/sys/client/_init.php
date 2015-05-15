<?

// set request
// --------------------------------------------------------------------------------------
   set::{'client.request'}
   ([
      "dom"=>strtolower($_SERVER['SERVER_NAME']),
      "act"=>strtolower($_SERVER['REQUEST_METHOD']),
      "uri"=>$_SERVER['REQUEST_URI'],
      "pth"=>rtrim(explode('?', explode('#', $_SERVER['REQUEST_URI'])[0])[0], '/'),
      "vrs"=>call(function()
      {
         $vrs = new obj();

         foreach ($_REQUEST as $key => $val)
         {
            if (strlen($val) > 0)
            { $vrs->$key = parse(frisk::input($val)); }
         }

         return $vrs;
      })
   ]);
// --------------------------------------------------------------------------------------

?>
