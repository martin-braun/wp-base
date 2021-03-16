(function () {
  const preloaderClassName = null;

  /**
   * WAITER HELPER
   */
  window.waitFor = function (
    conditionCallback,
    resolveCallback,
    samplingRate,
    waitTimeout,
    timeoutCallback
  ) {
    samplingRate = samplingRate || 100;
    let timeToLive = waitTimeout || Number.POSITIVE_INFINITY;
    const waitInterval = setInterval(function () {
      if (timeToLive <= 0) {
        clearInterval(waitInterval);
        timeoutCallback && timeoutCallback();
        return;
      }
      if (conditionCallback()) {
        clearInterval(waitInterval);
        resolveCallback();
        return;
      }
      timeToLive -= samplingRate;
    }, samplingRate);
  };

  /**
   * RAISE READY EVENT
   */
  (function (preloaderClassName) {
    function isPreloaderBlocking() {
      return (
        preloaderClassName &&
        document.getElementsByClassName(preloaderClassName).length > 0
      );
    }
    function isDocumentReady() {
      return (
        document.readyState === "complete" ||
        document.readyState === "interactive"
      );
    }
    function dispatchWhenDocumentReady(event) {
      if (isDocumentReady()) {
        setTimeout(function () {
          document.dispatchEvent(event);
        }, 1);
      } else {
        document.addEventListener("DOMContentLoaded", function () {
          document.dispatchEvent(event);
        });
      }
    }
    const readyEvent = new Event("ready");
    if (isPreloaderBlocking()) {
      window.waitFor(
        () => !isPreloaderBlocking(),
        () => dispatchWhenDocumentReady(readyEvent)
      );
    } else {
      dispatchWhenDocumentReady(readyEvent);
    }
    document.ready = function (fn) {
      if (!isPreloaderBlocking() && isDocumentReady()) {
        setTimeout(fn, 1);
      } else {
        document.addEventListener("ready", fn);
      }
    };
  })(preloaderClassName);
  
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
