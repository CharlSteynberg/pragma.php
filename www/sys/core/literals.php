<?

// types :: list of identifier types
// --------------------------------------------------------------------------------------
   function varTypes()
   {
      return
      [
         'udf'=>'undefined',
         'nul'=>'null',
         'str'=>'string',
         'int'=>'integer',
         'flt'=>'float',
         'bln'=>'boolean',
         'arr'=>'array',
         'obj'=>'object',
         'fnc'=>'function',
         'jsm'=>'jsam',
         'jsn'=>'json',
         'pth'=>'path',
      ];
   }
// --------------------------------------------------------------------------------------



// type constants
// --------------------------------------------------------------------------------------
   call_user_func
   (
      function()
      {
         $tps = varTypes();

         foreach ($tps as $k => $v)
         { define($k, $v); }
      }
   );
// --------------------------------------------------------------------------------------



// type :: simple data type identifier
// --------------------------------------------------------------------------------------
   function typeOf($d)
   {
      $t = strtolower(gettype($d));

      if ($t === 'double'){ return flt; }
      if ($d instanceof Closure){ return fnc; }

      return $t;
   }
// --------------------------------------------------------------------------------------



// is :: type casting
// --------------------------------------------------------------------------------------
   class is
   {
      public static function __callStatic($k,$v)
      {
         if (!isset(varTypes()[$k]))
         { throw new Exception('varType "'.$k.'" is undefined'); }

         return ((varTypes()[$k] === typeOf($v[0])) ? true : false);
      }
   }
// --------------------------------------------------------------------------------------



// obj :: nice objects
// --------------------------------------------------------------------------------------
   class obj
   {
      function __construct($atr=[])
      {
         $atr = (($atr === null) ? [] : $atr);

         foreach ($atr as $key => $val)
         { $this->$key = $val; }
      }

      public function __call($mth, $arg)
      {
         if (isset($this->$mth) && is_callable($this->$mth))
         {
            $arg[] = $this;
            return call_user_func_array($this->$mth, $arg);
         }
         else
         { throw new Exception('"'.$mth.'" is undefined'); }
      }
   }
// --------------------------------------------------------------------------------------



// str :: extended string methods
// --------------------------------------------------------------------------------------
   class str
   {
   // type :: type of string
   // -----------------------------------------------------------------------------------
      public static function typeOf($d)
      {
         $flc = $d[0].substr($d, -1, 1);
         $dcr = ['()'=>'jsam', '{}'=>'json', '{}'=>'json'];

         if (isset($drc[$flc]))
         { return $drc[$flc]; }

         $t = (($d[0] === '/') ? 'pub/'.$d : $d);

         if
         (
            (strpos($t,'/') !== false) &&
            preg_match('/^[a-zA-Z0-9-\/\._]+$/', $t) &&
            is_dir(explode('/', $t)[0])
         )
         { return 'path'; }

         if ($d === ''){ return 'null'; }

         return typeOf(parse::text($d));
      }
   // -----------------------------------------------------------------------------------

   // in :: return object with methods
   // -----------------------------------------------------------------------------------
      public static function in($d)
      {
         return new obj
         ([
         // get :: sub-string
         // -----------------------------------------------------------------------------
            'get'=>function($d,$o)
            {
               $t = typeOf($d);
               $v = $o->value;
               $l = strlen($v);

               if ($t === str)
               {
                  if ($d == '<>')
                  { return $v[0].substr($v, -1, 1); }

                  if ($d === '><')
                  { return substr($v, 1, -1); }

                  return null;
               }

               if ($t === flt)
               {
                  if ($d < 0.1)
                  { return $v; }

                  $v = $v.'';
                  $p = explode('.', $d);
                  $p = [($p[0] - 0), (0 - $p[1])];

                  return substr($v, $p[0], $p[1]);
               }

               return null;
            },
         // -----------------------------------------------------------------------------

         // has :: contains sub-string
         // -----------------------------------------------------------------------------
            'has'=>function($d,$o)
            {
               return ((strpos($o->value, $d) !== false) ? true : false);
            },
         // -----------------------------------------------------------------------------

         // find :: position of sub-string
         // -----------------------------------------------------------------------------
            'find'=>function($d,$o)
            {
               return strpos($o->value, $d);
            },
         // -----------------------------------------------------------------------------

         // value :: string sent to "in"
         // -----------------------------------------------------------------------------
            'value'=>$d
         // -----------------------------------------------------------------------------
         ]);
      }
   // --------------------------------------------------------------------------------------
   }
// --------------------------------------------------------------------------------------



// to :: type casting
// --------------------------------------------------------------------------------------
   class to
   {
   // str :: string
   // -----------------------------------------------------------------------------------
      public static function str($d)
      {
         $t = typeOf($d);

         if ($t === str) { return $d; }

         if ($d === null) { return 'null'; }
         if ($d === true) { return 'true'; }
         if ($d === false){ return 'false'; }

         if ($t === int) { return $d.''; }
         if ($t === flt) { return $d.''; }

         if (($t === arr) || ($t === obj))
         {
            $f = ((liveMode === false) ? JSON_PRETTY_PRINT : null);
            return str_replace('\\/', '/', json_encode($d, $f));
         }

      }
   // -----------------------------------------------------------------------------------


   // num :: number
   // -----------------------------------------------------------------------------------
      public static function int($d)
      {
         $t = typeOf($d);

         if ($t === int)
         { return $d; }

         if ($t === nul)
         { return 0; }

         if ($t === str)
         {
            if (is_numeric($d))
            { return ($d -0); }
            else
            { return null; }
         }

         if ($t === bln)
         { return (($d === true) ? 1 : 0); }

         return null;
      }
   // -----------------------------------------------------------------------------------


   // bln :: boolean
   // -----------------------------------------------------------------------------------
      public static function bln($d)
      {
         $t = typeOf($d);

         if ($t === nul)
         { return false; }

         if ($t === bln)
         { return $d; }

         if ($t === num)
         {
            if ($d === 1){ return true; }
            if ($d === 0){ return false; }
         }

         if ($t === str)
         {
            if ($d === 'true'){ return true; }
            if ($d === 'false'){ return false; }

            if ($d === 'yes'){ return true; }
            if ($d === 'no'){ return false; }

            if ($d === 'on'){ return true; }
            if ($d === 'off'){ return false; }

            if ($d === '1'){ return true; }
            if ($d === '0'){ return false; }
         }

         return null;
      }
   // -----------------------------------------------------------------------------------


   // arr :: array
   // -----------------------------------------------------------------------------------
      public static function arr($d)
      {
         $t = typeOf($d);

         if ($t === nul){ $d = array(); }
         if ($t === obj){ $d = ((array)$d); }

         if (typeOf($d) === arr)
         {
            reset($d);
            return $d;
         }

         return null;
      }
   // -----------------------------------------------------------------------------------


   // obj :: object
   // -----------------------------------------------------------------------------------
      public static function obj($d)
      {
         $t = typeOf($d);

         if ($t === obj)
         { return $d; }

         if (($d === null) || ($t === arr))
         { return new obj($d); }

         return null;
      }
   // -----------------------------------------------------------------------------------
   }
// --------------------------------------------------------------------------------------

?>
