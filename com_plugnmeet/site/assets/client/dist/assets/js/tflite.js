var createTFLiteModule=(()=>{var n="undefined"!==typeof document&&document.currentScript?document.currentScript.src:void 0;return function(t){var e,r,i="undefined"!==typeof(t=t||{})?t:{},u=Object.assign;i.ready=new Promise((function(n,t){e=n,r=t}));var a,o=u({},i),l=[],c="./this.program",s=(n,t)=>{throw t},f="";"undefined"!==typeof document&&document.currentScript&&(f=document.currentScript.src),n&&(f=n),f=0!==f.indexOf("blob:")?f.substr(0,f.replace(/[?#].*/,"").lastIndexOf("/")+1):"";var p=i.print||console.log.bind(console),m=i.printErr||console.warn.bind(console);u(i,o),o=null,i.arguments&&(l=i.arguments),i.thisProgram&&(c=i.thisProgram),i.quit&&(s=i.quit);var d;i.wasmBinary&&(d=i.wasmBinary);var _,y=i.noExitRuntime||!0;"object"!==typeof WebAssembly&&W("no native wasm support detected");var g=!1;var h="undefined"!==typeof TextDecoder?new TextDecoder("utf8"):void 0;function v(n,t,e){for(var r=t+e,i=t;n[i]&&!(i>=r);)++i;if(i-t>16&&n.subarray&&h)return h.decode(n.subarray(t,i));for(var u="";t<i;){var a=n[t++];if(128&a){var o=63&n[t++];if(192!=(224&a)){var l=63&n[t++];if((a=224==(240&a)?(15&a)<<12|o<<6|l:(7&a)<<18|o<<12|l<<6|63&n[t++])<65536)u+=String.fromCharCode(a);else{var c=a-65536;u+=String.fromCharCode(55296|c>>10,56320|1023&c)}}else u+=String.fromCharCode((31&a)<<6|o)}else u+=String.fromCharCode(a)}return u}function j(n,t){return n?v(C,n,t):""}var w,b,C,A;"undefined"!==typeof TextDecoder&&new TextDecoder("utf-16le");function I(n){w=n,i.HEAP8=b=new Int8Array(n),i.HEAP16=new Int16Array(n),i.HEAP32=A=new Int32Array(n),i.HEAPU8=C=new Uint8Array(n),i.HEAPU16=new Uint16Array(n),i.HEAPU32=new Uint32Array(n),i.HEAPF32=new Float32Array(n),i.HEAPF64=new Float64Array(n)}i.INITIAL_MEMORY;var R,M=[],O=[],E=[];function S(){return y||!1}var k=0,x=null,T=null;function W(n){i.onAbort&&i.onAbort(n),m(n="Aborted("+n+")"),g=!0,1,n+=". Build with -s ASSERTIONS=1 for more info.";var t=new WebAssembly.RuntimeError(n);throw r(t),t}i.preloadedImages={},i.preloadedAudios={};var H,P;function D(n){return n.startsWith("data:application/octet-stream;base64,")}function F(n){try{if(n==H&&d)return new Uint8Array(d);if(a)return a(n);throw"both async and sync fetching of the wasm failed"}catch(m){W(m)}}function L(n){for(;n.length>0;){var t=n.shift();if("function"!=typeof t){var e=t.func;"number"===typeof e?void 0===t.arg?q(e)():q(e)(t.arg):e(void 0===t.arg?null:t.arg)}else t(i)}}D(H="tflite.wasm")||(P=H,H=i.locateFile?i.locateFile(P,f):f+P);var U,B=[];function q(n){var t=B[n];return t||(n>=B.length&&(B.length=n+1),B[n]=t=R.get(n)),t}function z(n){try{return _.grow(n-w.byteLength+65535>>>16),I(_.buffer),1}catch(t){}}var N={};function G(){if(!G.strings){var n={USER:"web_user",LOGNAME:"web_user",PATH:"/",PWD:"/",HOME:"/home/web_user",LANG:("object"===typeof navigator&&navigator.languages&&navigator.languages[0]||"C").replace("-","_")+".UTF-8",_:c||"./this.program"};for(var t in N)void 0===N[t]?delete n[t]:n[t]=N[t];var e=[];for(var t in n)e.push(t+"="+n[t]);G.strings=e}return G.strings}var V={mappings:{},buffers:[null,[],[]],printChar:function(n,t){var e=V.buffers[n];0===t||10===t?((1===n?p:m)(v(e,0)),e.length=0):e.push(t)},varargs:void 0,get:function(){return V.varargs+=4,A[V.varargs-4>>2]},getStr:function(n){return j(n)},get64:function(n,t){return n}};var Y,J={_dlopen_js:function(n,t){W("To use dlopen, you need to use Emscripten's linking support, see https://github.com/emscripten-core/emscripten/wiki/Linking")},_dlsym_js:function(n,t){W("To use dlopen, you need to use Emscripten's linking support, see https://github.com/emscripten-core/emscripten/wiki/Linking")},abort:function(){W("")},clock_gettime:function(n,t){var e,r;if(0===n)e=Date.now();else{if(1!==n&&4!==n)return r=28,A[K()>>2]=r,-1;e=U()}return A[t>>2]=e/1e3|0,A[t+4>>2]=e%1e3*1e3*1e3|0,0},emscripten_get_heap_max:function(){return 2147483648},emscripten_get_now:U=()=>performance.now(),emscripten_memcpy_big:function(n,t,e){C.copyWithin(n,t,t+e)},emscripten_resize_heap:function(n){var t,e,r=C.length,i=2147483648;if((n>>>=0)>i)return!1;for(var u=1;u<=4;u*=2){var a=r*(1+.2/u);if(a=Math.min(a,n+100663296),z(Math.min(i,((t=Math.max(n,a))%(e=65536)>0&&(t+=e-t%e),t))))return!0}return!1},environ_get:function(n,t){var e=0;return G().forEach((function(r,i){var u=t+e;A[n+4*i>>2]=u,function(n,t,e){for(var r=0;r<n.length;++r)b[t++>>0]=n.charCodeAt(r);e||(b[t>>0]=0)}(r,u),e+=r.length+1})),0},environ_sizes_get:function(n,t){var e=G();A[n>>2]=e.length;var r=0;return e.forEach((function(n){r+=n.length+1})),A[t>>2]=r,0},exit:function(n){!function(n,t){n,S()||!0;!function(n){n,S()||(i.onExit&&i.onExit(n),g=!0);s(n,new Q(n))}(n)}(n)},fd_close:function(n){return 0},fd_seek:function(n,t,e,r,i){},fd_write:function(n,t,e,r){for(var i=0,u=0;u<e;u++){var a=A[t>>2],o=A[t+4>>2];t+=8;for(var l=0;l<o;l++)V.printChar(n,C[a+l]);i+=o}return A[r>>2]=i,0},getentropy:function n(t,e){n.randomDevice||(n.randomDevice=function(){if("object"===typeof crypto&&"function"===typeof crypto.getRandomValues){var n=new Uint8Array(1);return function(){return crypto.getRandomValues(n),n[0]}}return function(){W("randomDevice")}}());for(var r=0;r<e;r++)b[t+r>>0]=n.randomDevice();return 0},setTempRet0:function(n){n}},K=(function(){var n={env:J,wasi_snapshot_preview1:J};function t(n,t){var e,r=n.exports;i.asm=r,I((_=i.asm.memory).buffer),R=i.asm.__indirect_function_table,e=i.asm.__wasm_call_ctors,O.unshift(e),function(n){if(k--,i.monitorRunDependencies&&i.monitorRunDependencies(k),0==k&&(null!==x&&(clearInterval(x),x=null),T)){var t=T;T=null,t()}}()}function e(n){t(n.instance)}function u(t){return(d||"function"!==typeof fetch?Promise.resolve().then((function(){return F(H)})):fetch(H,{credentials:"same-origin"}).then((function(n){if(!n.ok)throw"failed to load wasm binary file at '"+H+"'";return n.arrayBuffer()})).catch((function(){return F(H)}))).then((function(t){return WebAssembly.instantiate(t,n)})).then((function(n){return n})).then(t,(function(n){m("failed to asynchronously prepare wasm: "+n),W(n)}))}if(k++,i.monitorRunDependencies&&i.monitorRunDependencies(k),i.instantiateWasm)try{return i.instantiateWasm(n,t)}catch(a){return m("Module.instantiateWasm callback failed with error: "+a),!1}(d||"function"!==typeof WebAssembly.instantiateStreaming||D(H)||"function"!==typeof fetch?u(e):fetch(H,{credentials:"same-origin"}).then((function(t){return WebAssembly.instantiateStreaming(t,n).then(e,(function(n){return m("wasm streaming compile failed: "+n),m("falling back to ArrayBuffer instantiation"),u(e)}))}))).catch(r)}(),i.___wasm_call_ctors=function(){return(i.___wasm_call_ctors=i.asm.__wasm_call_ctors).apply(null,arguments)},i._getModelBufferMemoryOffset=function(){return(i._getModelBufferMemoryOffset=i.asm.getModelBufferMemoryOffset).apply(null,arguments)},i._getInputMemoryOffset=function(){return(i._getInputMemoryOffset=i.asm.getInputMemoryOffset).apply(null,arguments)},i._getInputHeight=function(){return(i._getInputHeight=i.asm.getInputHeight).apply(null,arguments)},i._getInputWidth=function(){return(i._getInputWidth=i.asm.getInputWidth).apply(null,arguments)},i._getInputChannelCount=function(){return(i._getInputChannelCount=i.asm.getInputChannelCount).apply(null,arguments)},i._getOutputMemoryOffset=function(){return(i._getOutputMemoryOffset=i.asm.getOutputMemoryOffset).apply(null,arguments)},i._getOutputHeight=function(){return(i._getOutputHeight=i.asm.getOutputHeight).apply(null,arguments)},i._getOutputWidth=function(){return(i._getOutputWidth=i.asm.getOutputWidth).apply(null,arguments)},i._getOutputChannelCount=function(){return(i._getOutputChannelCount=i.asm.getOutputChannelCount).apply(null,arguments)},i._loadModel=function(){return(i._loadModel=i.asm.loadModel).apply(null,arguments)},i._runInference=function(){return(i._runInference=i.asm.runInference).apply(null,arguments)},i._free=function(){return(i._free=i.asm.free).apply(null,arguments)},i._malloc=function(){return(i._malloc=i.asm.malloc).apply(null,arguments)},i.___errno_location=function(){return(K=i.___errno_location=i.asm.__errno_location).apply(null,arguments)});i.___dl_seterr=function(){return(i.___dl_seterr=i.asm.__dl_seterr).apply(null,arguments)},i._emscripten_main_thread_process_queued_calls=function(){return(i._emscripten_main_thread_process_queued_calls=i.asm.emscripten_main_thread_process_queued_calls).apply(null,arguments)},i._memalign=function(){return(i._memalign=i.asm.memalign).apply(null,arguments)},i.stackSave=function(){return(i.stackSave=i.asm.stackSave).apply(null,arguments)},i.stackRestore=function(){return(i.stackRestore=i.asm.stackRestore).apply(null,arguments)},i.stackAlloc=function(){return(i.stackAlloc=i.asm.stackAlloc).apply(null,arguments)},i.dynCall_jjj=function(){return(i.dynCall_jjj=i.asm.dynCall_jjj).apply(null,arguments)},i.dynCall_jiii=function(){return(i.dynCall_jiii=i.asm.dynCall_jiii).apply(null,arguments)},i.dynCall_iiiijj=function(){return(i.dynCall_iiiijj=i.asm.dynCall_iiiijj).apply(null,arguments)},i.dynCall_viijj=function(){return(i.dynCall_viijj=i.asm.dynCall_viijj).apply(null,arguments)},i.dynCall_viiijjjj=function(){return(i.dynCall_viiijjjj=i.asm.dynCall_viiijjjj).apply(null,arguments)},i.dynCall_iijjiiii=function(){return(i.dynCall_iijjiiii=i.asm.dynCall_iijjiiii).apply(null,arguments)},i.dynCall_jiji=function(){return(i.dynCall_jiji=i.asm.dynCall_jiji).apply(null,arguments)};function Q(n){this.name="ExitStatus",this.message="Program terminated with exit("+n+")",this.status=n}function X(n){function t(){Y||(Y=!0,i.calledRun=!0,g||(!0,L(O),e(i),i.onRuntimeInitialized&&i.onRuntimeInitialized(),function(){if(i.postRun)for("function"==typeof i.postRun&&(i.postRun=[i.postRun]);i.postRun.length;)n=i.postRun.shift(),E.unshift(n);var n;L(E)}()))}n=n||l,k>0||(!function(){if(i.preRun)for("function"==typeof i.preRun&&(i.preRun=[i.preRun]);i.preRun.length;)n=i.preRun.shift(),M.unshift(n);var n;L(M)}(),k>0||(i.setStatus?(i.setStatus("Running..."),setTimeout((function(){setTimeout((function(){i.setStatus("")}),1),t()}),1)):t()))}if(T=function n(){Y||X(),Y||(T=n)},i.run=X,i.preInit)for("function"==typeof i.preInit&&(i.preInit=[i.preInit]);i.preInit.length>0;)i.preInit.pop()();return X(),t.ready}})();"object"===typeof exports&&"object"===typeof module?module.exports=createTFLiteModule:"function"===typeof define&&define.amd?define([],(function(){return createTFLiteModule})):"object"===typeof exports&&(exports.createTFLiteModule=createTFLiteModule);