(function () {
  var preloaderClassName = 'preloader';

  /**
   * RAISE READY EVENT
   */
  var readyEvent = new Event("ready");
  var readyInterval = setInterval(function () {
    if (document.getElementsByClassName(preloaderClassName).length < 1) {
      clearInterval(readyInterval);
      if (
        document.readyState === "complete" ||
        document.readyState === "interactive"
      ) {
        setTimeout(function () {
          document.dispatchEvent(readyEvent);
        }, 1);
      } else {
        document.addEventListener("DOMContentLoaded", function () {
          document.dispatchEvent(readyEvent);
        });
      }
    }
	}, 100);
  document.ready = function(fn) {
    if(document.getElementsByClassName(preloaderClassName).length < 1 
      && (document.readyState === "complete" || document.readyState === "interactive")) {
      setTimeout(fn, 1);
    }
    else {
      document.addEventListener("ready", fn);
    }
  }
  
})();

document.ready(function () {

  /**
   * INSERT CURRENT YEAR IN FOOTER
   */
  (function () {
    var footerCopyrightCurrentYearElement = document.getElementById(
      "footer_copyright_current_year"
    );
    if (footerCopyrightCurrentYearElement) {
      footerCopyrightCurrentYearElement.innerText = new Date().getFullYear();
    }
  })();

});
