
// bind keyboard key: ` to toggle devHud
// --------------------------------------------------------------------------------------
   keyboard.bind(192, function()
   {
      var hud = get('devHud');

      if (hud.status == 'off')
      {
         hud.style.display = 'block';
         hud.set({status:'on'});
      }
      else
      {
         hud.style.display = 'none';
         hud.set({status:'off'});
      }
   });
// --------------------------------------------------------------------------------------



// devHud functionality
// --------------------------------------------------------------------------------------
   global.add
   ({
      devHud:
      {
      // navigate devHud menu
      // --------------------------------------------------------------------------------
         togl:function(oid)
         {
            var stg = get('devHudStg');

            if (!stg.show)
            { stg.set({show:oid}); }

            get(stg.show).style.display = 'none';
            get(oid).style.display = 'initial';
         },
      // --------------------------------------------------------------------------------


      // convert css icon names & values to jason
      // --------------------------------------------------------------------------------
         css2jsn:function(oid)
         {
         // locals
         // -----------------------------------------------------------------------------
            var jsn, pos, stg = get(oid);
         // -----------------------------------------------------------------------------


         // strip white-space
         // -----------------------------------------------------------------------------
            jsn = (function(txt)
            {
               var bfr = '';
               var omt = {" ":1,"\t":1,"\n":1,"\r":1,"}":1};
               var rpl = ['.icon-','.ico-','.fa-','.i-','";'];
               var tst = ':before{content:"\\';
               var pts = null;
               var rsl = {};

               var c = '';

               for (var i=0; i<txt.length; i++)
               {
                  c = txt[i];

                  if (omt[c]){ continue; }
                  if (c == '.'){ bfr += c; }else if (bfr.length > 0){ bfr += c; }

                  if (c == ';')
                  {
                     if ((bfr[0] == '.') && (bfr.indexOf(tst) > 0))
                     {
                        for (var r in rpl) { bfr = bfr.split(rpl[r]).join(''); }
                        pts = bfr.split(tst);
                        rsl[pts[0]] = pts[1];
                     }

                     bfr = '';
                  }
               }

               return rsl;

            }(stg.value));
         // -----------------------------------------------------------------------------


         // return result
         // -----------------------------------------------------------------------------
            stg.value = JSON.stringify(jsn);
         // -----------------------------------------------------------------------------
         },
      // --------------------------------------------------------------------------------
      }
   });
// --------------------------------------------------------------------------------------
