<?

// path folder/file info
// -----------------------------------------------------------------------------------------
   Path::set('info', function($pth)
   {
      $rsl = new obj
      ([
         'stat'=>200,
         'type'=>null,
         'root'=>null,
         'home'=>null,
         'base'=>null,
         'name'=>null,
         'extn'=>null,
         'size'=>null,
         'mime'=>null,
         'char'=>null,
         'bnry'=>null,
      ]);

      if (!is_readable(CWD.'/'.$pth))
      { $rsl->stat = 403; }

      $pts = explode('/', $pth);

      if (is_dir(CWD.'/'.$pth))
      {
         $rsl->type = 'dir';
         $rsl->root = $pts[0];
         $rsl->base = array_pop($pts);
         $rsl->name = $rsl->base;
         $rsl->home = implode($pts, '/');

         return $rsl;
      }

      if (!file_exists(CWD.'/'.$pth))
      { $rsl->stat = 404; }

      $inf = (object)pathinfo(CWD.'/'.$pth);
      $inf->dirname = trim(str_replace(CWD, '', $inf->dirname), '/');

      $rsl->type = 'path';
      $rsl->root = $pts[0];
      $rsl->home = $inf->dirname;
      $rsl->base = $inf->basename;
      $rsl->name = $inf->filename;
      $rsl->extn = (isset($inf->extension) ? $inf->extension : null);

      if ($rsl->stat === 200)
      {
         $rsl->type = 'file';
         $flh = fopen(CWD.'/'.$pth, "r");
         $str = fread($flh, 512);
         $flh = fclose($flh); clearstatcache();
         $enc = strtolower(mb_detect_encoding($str, mb_list_encodings(), true));

         $rsl->size = filesize(CWD.'/'.$pth);
         $rsl->mime = path::get('conf.mime')->{$rsl->extn};
         $rsl->char = $enc;
         $rsl->bnry = ((($enc === 'ascii') || ($enc === 'utf-8')) ? false : true);
      }

      return $rsl;
   });
// -----------------------------------------------------------------------------------------

?>
