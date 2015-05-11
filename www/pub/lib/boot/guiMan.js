
// strict mode
// --------------------------------------------------------------------------------------
   "use strict";
// --------------------------------------------------------------------------------------



// http :: server communications
// --------------------------------------------------------------------------------------
   window.http = new Object
   ({
      new:function(mth,pth)
      {
         var xhr = new XMLHttpRequest();
         xhr.open(mth.toUpperCase(), pth, true);
         return xhr;
      },

      get:function(pth,vrs,cbf)
      {
         var xhr = this.new('get', pth);

         xhr.setRequestHeader('flapjack', 'yabadoo');
         xhr.send();
      },

      put:function(pth,vrs,cbf)
      {},

      post:function(frm,cbf)
      {},

      head:function(pth,vrs,cbf)
      {},
   })
// --------------------------------------------------------------------------------------
