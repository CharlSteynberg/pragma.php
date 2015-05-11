<?

   set::{"client.boot"}(function()
   {
      $bcj = path::read('');
      $vrs = obj(['reqPth'=>client::get('request.pth')]);

      return path::read('pub/doc/client/boot.jsam', $vrs);
   });

?>
