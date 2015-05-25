
// global :: object reference
// --------------------------------------------------------------------------------------
   global = window;
// --------------------------------------------------------------------------------------


// typeOf :: literals identity
// --------------------------------------------------------------------------------------
   global.typeOf = function(dfn)
   {
      var tpe = (typeof arg).toLowerCase();

      if (tpe === 'number')
      {
         if (isNaN(dfn))
         { tpe = 'nan'; }

         if ((dfn+'').indexOf('.') > -1)
         { tpe = 'float'; }
      }
      else
      {
         tpe = ({}).toString.call(dfn).match(/\s([a-zA-Z]+)/)[1].toLowerCase();

         if (tpe.indexOf('element') > -1)
         { tpe = 'element'; }
      }

      return tpe;
   };
// --------------------------------------------------------------------------------------



// extend :: object functionality
// --------------------------------------------------------------------------------------
   global.extend = function(src)
   {
      var opt = {writeable:false, enumerable:false, configurable:false};
      var rsl = {};

      rsl.pty = function(nme, fnc)
      {
         opt.get = fnc;
         Object.defineProperty(src, nme, opt);
      };

      rsl.fnc = function(nme, fnc)
      {
         opt.value = fnc;
         Object.defineProperty(src, nme, opt);
      };

      return rsl;
   };
// --------------------------------------------------------------------------------------



// has :: string.has(substring)
// --------------------------------------------------------------------------------------
   extend(String.prototype).fnc('has', function(str)
   {
      var dfn = this.toString();

      if (dfn.indexOf(str) > -1)
      { return true; }

      return false;
   });
// --------------------------------------------------------------------------------------



// keys :: object.keys[0]
// --------------------------------------------------------------------------------------
   // extend(Object.prototype).pty('key', function()
   // {
   //    var dfn = this;
   //    var rsl = [];
   //
   //    for (var i=0; i < Object.keys(dfn).length; i++)
   //    {
   //       rsl[i] = Object.keys(dfn)[i];
   //    }
   //
   //    return rsl;
   // });
// --------------------------------------------------------------------------------------



// add :: global extension
// --------------------------------------------------------------------------------------
   global.add = function(dfn)
   {
      for (var k in dfn)
      { global[k] = dfn[k]; }
   };
// --------------------------------------------------------------------------------------



// keyboard :: bind keys
// --------------------------------------------------------------------------------------
   global.add
   ({
      keyboard:
      {
         bond:{},

         bind:function(num, fnc)
         {
            this.bond[num] = fnc;
         },

         grab:function(evt)
         {
            var num = evt.keyCode;

            if (keyboard.bond.has(num))
            { keyboard.bond[num](); }
         }
      }
   });

   document.addEventListener("keydown", keyboard.grab, false);
// --------------------------------------------------------------------------------------



// set :: object.set({property:value})
// --------------------------------------------------------------------------------------
   extend(Object.prototype).fnc('has', function(dfn)
   {
      if (this.hasOwnProperty(dfn))
      { return true; }

      return false;
   });
// --------------------------------------------------------------------------------------



// has :: object.has('pty')
// --------------------------------------------------------------------------------------
   extend(Object.prototype).fnc('set', function(dfn)
   {
      if (typeOf(dfn) != 'object')
      { throw new TypeError('object.set('+typeOf(dfn)+') :: object expected'); }

      for (var p in dfn)
      {
         this[p] = dfn[p];

         if (typeOf(this).has('element'))
         { this.setAttribute(p, dfn[p]); }
      }
   });
// --------------------------------------------------------------------------------------



// get :: elements by: id, name, class
// --------------------------------------------------------------------------------------
   global.add
   ({
      get:function(str)
      {
         var chr = str[0];
         var rsl = null;
         var tpe = null;
         var atr = null;

         if ((chr == '#') || (chr == '.'))
         { str = str.substr(1,str.length); }
         else
         { chr = null; }

         if ((chr == null) || (chr == '#'))
         {
            rsl = document.getElementById(str);

            if (!rsl)
            { rsl = document.getElementsByName(str); }
         }
         else
         { rsl = document.getElementsByClassName(str); }

         if (typeOf(rsl) == 'htmlcollection')
         { rsl = [].slice.call(rsl); }

         tpe = typeOf(rsl);

         if ((tpe !== 'array') && (!tpe.has('element')))
         { return null; }

         if (tpe.has('element'))
         {
            [].slice.call(rsl.attributes).forEach(function(itm)
            {
               rsl[itm.name] = itm.value;
            });
         }

         return rsl;
      }
   });
// --------------------------------------------------------------------------------------



// toggle show / hide
// --------------------------------------------------------------------------------------
   global.add
   ({
      togl:function(dfn)
      {
         dfn = ((typeOf(dfn) == 'string') ? get(dfn) : dfn);

         var fnc = ((dfn.className.indexOf('hide') < 0) ? 'hide' : 'show');

         global[fnc](dfn);
      },

      show:function(dfn)
      {
         dfn = ((typeOf(dfn) == 'string') ? get(dfn) : dfn);
         dfn = ((typeOf(dfn) != 'array') ? [dfn] : dfn);

         dfn.forEach
         (
            function(i,k,a)
            {
               var c = i.className.split(' hide').join('');
                   c = c.split('hide ').join('');
                   c = c.split('hide').join('');

               i.className = c + ' show';
            }
         );
      },

      hide:function(dfn)
      {
         dfn = ((typeOf(dfn) == 'string') ? get(dfn) : dfn);
         dfn = ((typeOf(dfn) != 'array') ? [dfn] : dfn);

         dfn.forEach
         (
            function(i,k,a)
            {
               var c = i.className.split(' show').join('');
                   c = c.split('show ').join('');
                   c = c.split('show').join('');

               i.className = c + ' hide';
            }
         );
      }
   });
// --------------------------------------------------------------------------------------
