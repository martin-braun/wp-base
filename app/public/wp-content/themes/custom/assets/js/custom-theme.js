(function () {
  const preloaderClassName = null;

  /**
   * WAITER HELPER
   */
  window.waitFor = function (
    condition,
    resolve,
    samplingRate,
    waitTimeout,
    reject
  ) {
    samplingRate = samplingRate || 100;
    let timeToLive = waitTimeout || Number.POSITIVE_INFINITY;
    const waitInterval = setInterval(function () {
      if (timeToLive <= 0) {
        clearInterval(waitInterval);
        timeoutCallback && reject();
        return;
      }
      if (condition()) {
        clearInterval(waitInterval);
        resolve();
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

  /**
   * LAZY LOAD HEAVY ELEMENTS ON SCROLL
   */
  (function () {
    window.heavy = window.heavy || {};
    var heavyContainers = document.querySelectorAll(".heavy-container");
    var attemptInitHeavyElements = function () {
      var heavyContainersToLoad = [];
      heavyContainers.forEach(function (container) {
        if (
          window.heavy[container.id] &&
          container.getBoundingClientRect().top - container.dataset.offset <
            window.innerHeight
        ) {
          heavyContainersToLoad.push(container);
        }
      });
      if (heavyContainersToLoad.length) {
        heavyContainersToLoad.forEach(function (container) {
          container.innerHTML = window.heavy[container.id];
          container.onload && container.onload();
          delete window.heavy[container.id];
          container.classList.add("heavy-container--loaded");
        });
      }
    }
    document.addEventListener("scroll", attemptInitHeavyElements);
    attemptInitHeavyElements();
  })();

  /**
   * THE NEWSLETTER PLUGIN AJAX SUBSCRIPTION
   */
  (function () {
    /**
     *
     * @param {string} email Email address to subscribe.
     * @param {string} firstName Name or first name, which is only required when configured to be required.
     * @param {string} surname Last name, which is only required when configured to be required.
     * @param {string} gender Sex, which is only required when configured to be required.
     * @param {Array<string>} more Additional fields in the configured order.
     * @param {function(string)} resolve Success callback with p.tnp-msg element-encapsulated status message.
     * @param {function(string)} reject Error callback with status code.
     */
    window.tnp_subscribe = function (
      email,
      firstName,
      surname,
      gender,
      more,
      resolve,
      reject
    ) {
      var xhr = new XMLHttpRequest();
      xhr.withCredentials = true;
      var data = [
        "ts=" + Math.floor(Date.now() / 1000),
        "nx=" + (firstName ? encodeURIComponent(firstName) : ""),
        "ns=" + (surname ? encodeURIComponent(surname) : ""),
        "ne=" + (email ? encodeURIComponent(email) : ""),
        "nx=" + (gender || "n"),
        "ny=1",
      ];
      if (more) {
        for (var i = 0; i++; i < more.length) {
          data.push("np" + i + "=" + encodeURIComponent(more[i]));
        }
      }
      xhr.addEventListener("load", function (e) {
        if (resolve) {
          var doc = document.createElement("html");
          doc.innerHTML = xhr.responseText;
          var p = doc.getElementsByClassName("tnp-msg")[0];
          resolve(p ? p.innerText : p);
        }
      });
      xhr.addEventListener("error", function (e) {
        reject(xhr.status);
      });
      xhr.open("POST", "/?na=s");
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      xhr.send(data.join("&").replace(/%20/g, "+"));
    };

    // WooCommerce integration.
    // Find a checkbox with the ID "tnp_subscribe" and check it when the page loads.
    // Also call the "tnp_subscribe" function when the button with the id "place_order" is clicked.
    var tnp_subscribe_checkbox = document.getElementById("tnp_subscribe");
    var legal_checkbox = document.getElementById("legal");
    if (tnp_subscribe_checkbox && legal_checkbox) {
      tnp_subscribe_checkbox.checked = true;
      document.addEventListener("click", function (e) {
        if (
          e.target.id == "place_order" &&
          tnp_subscribe_checkbox.checked &&
          legal_checkbox.checked
        ) {
          window.tnp_subscribe(
            document.getElementById("billing_email").value,
            document.getElementById("billing_first_name").value,
            document.getElementById("billing_last_name").value,
            document.getElementById("billing_title").value == "1"
              ? "m"
              : document.getElementById("billing_title").value == "2"
              ? "f"
              : "n",
            [],
            console.log,
            console.error
          );
        }
      });
    }
  })();
});
