<?

// trap :: sp@mD0p3 : handle spambots
// --------------------------------------------------------------------------------------
   call
   (
      function()
      {
         $tst = hold::get('foo');
         $dis = path::parse('cfg/site/sys/robots.txt.jso')->{'text/plain+bot'};

         $pth = trim(site::get('request.pth'), 'pub/');
         $pth = str_replace('.jso', '.php', $pth);

         foreach ($dis as $dny)
         {
            $key = key($dny);
            $dny = trim($dny->$key, '/');

            if (($key === 'Disallow') && ($pth === $dny))
            {
               $vrs = obj(['page'=>explode('.',$dny)[0]]);

               site::render(path::read('cfg/site/htm/trap.htm.jso',$vrs));
               return;
            }
         }
      }
   );
// --------------------------------------------------------------------------------------

?>
