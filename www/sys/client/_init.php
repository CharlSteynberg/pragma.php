<?

// set request
// --------------------------------------------------------------------------------------
   call
   (
      function()
      {
         $pth = rtrim($_SERVER['REQUEST_URI'], '/');
         $pth = explode('?', $pth)[0];

         if (strpos($pth, '.') !== false)
         {
            $pts = explode('.', $_SERVER['REQUEST_URI']);
            $pth = $pts[0];
            $pts = explode('/', $pts[1]);
            $pth .= '.'.$pts[0];
         }

         set::{'client.request'}
         ([
            "dom"=>strtolower($_SERVER['SERVER_NAME']),
            "act"=>strtolower($_SERVER['REQUEST_METHOD']),
            "uri"=>$_SERVER['REQUEST_URI'],
            "pth"=>$pth,
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
      }
   );
// --------------------------------------------------------------------------------------

?>
