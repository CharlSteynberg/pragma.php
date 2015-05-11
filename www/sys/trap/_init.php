<?

// trap :: sp@mD0p3 : handle spambots
// --------------------------------------------------------------------------------------
   call
   (
      function()
      {
         $dis = parse::file('cfg/server/robots.jsam')->{'text/plain+bot'};
         $req = client::get('request');

         foreach ($dis as $dny)
         {
            $key = key($dny);
            $dny = trim($dny->$key, '/');
            $pth = trim($req->pth, '/');

            if (($key === 'Disallow') && ($pth === $dny))
            {
               $vrs = new obj(['page'=>explode('.',$dny)[0]]);

               server::respond(path::read('pub/doc/trap/default.jsam',$vrs));
               return;
            }
         }
      }
   );
// --------------------------------------------------------------------------------------

?>
