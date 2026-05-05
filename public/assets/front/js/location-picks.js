"use strict";

/**
 * Button/chip-based country → state → city picker for listing sidebar.
 * Syncs #country, #state, #city hidden fields and calls updateUrl().
 * Uses window.countryUrl / getStateUrl / getCityUrl so the script works when loaded
 * as an external file (const in another script is not always visible cross-file).
 */
function lpCountryUrl() {
  if (typeof window !== "undefined" && window.countryUrl) {
    return window.countryUrl;
  }
  try {
    return typeof countryUrl !== "undefined" ? countryUrl : "";
  } catch (e) {
    return "";
  }
}
function destroyListingLocationSelect2() {
  ["#countryDropdown", "#stateDropdown", "#cityDropdown"].forEach(function (sel) {
    var $el = $(sel);
    if ($el.length && $el.data("select2")) {
      try {
        $el.select2("destroy");
      } catch (e) {
        /* ignore */
      }
    }
  });
}

function debounce(fn, ms) {
  var t;
  return function () {
    var ctx = this,
      args = arguments;
    clearTimeout(t);
    t = setTimeout(function () {
      fn.apply(ctx, args);
    }, ms);
  };
}

window.initLocationPicks = function () {
  var $root = $("#location-picks");
  if (!$root.length) {
    return;
  }

  if (!lpCountryUrl()) {
    console.error(
      "[location-picks] countryUrl is missing. Ensure the listings page sets window.countryUrl (route frontend.get_country) before location-picks.js."
    );
    return;
  }

  destroyListingLocationSelect2();

  var $countries = $root.find("[data-location-picks='countries']");
  var $states = $root.find("[data-location-picks='states']");
  var $cities = $root.find("[data-location-picks='cities']");
  var $secState = $root.find("[data-location-picks='section-state']");
  var $secCity = $root.find("[data-location-picks='section-city']");
  var $countrySearch = $root.find("[data-location-picks='country-search']");
  var $btnMore = $root.find("[data-location-picks='country-more']");
  var countryPage = 1;
  var countryHasMore = false;
  var countrySearchTerm = "";
  var allLabel = $root.attr("data-label-all") || "All";

  function loadCountries(reset) {
    if (reset) {
      countryPage = 1;
    }
    $.getJSON(lpCountryUrl(), { search: countrySearchTerm, page: countryPage })
      .done(function (data) {
        var results = data.results || [];
        if (countryPage === 1) {
          $countries.empty();
        }
        results.forEach(function (r) {
          var active = String($("#country").val()) === String(r.id);
          var $b = $("<button/>", {
            type: "button",
            class: "location-picks__chip" + (active ? " is-active" : ""),
            "data-id": r.id,
            "data-kind": "country",
          }).text(r.name);
          $countries.append($b);
        });
        countryHasMore = data.more === true;
        $btnMore.toggle(countryHasMore);
      })
      .fail(function (jqXHR, textStatus, err) {
        console.error("[location-picks] get-country failed:", textStatus, err, jqXHR && jqXHR.status, lpCountryUrl());
      });
  }

  function renderStatesFromAjax(data) {
    $states.empty();
    $cities.empty();
    $secCity.hide();

    if (!data || (!data.states && !data.cities)) {
      $secState.hide();
      return;
    }

    if (data.states && data.states.length > 0) {
      $secState.show();
      var $allSt = $("<button/>", {
        type: "button",
        class: "location-picks__chip location-picks__chip--muted",
        "data-kind": "state",
        "data-all": "true",
      }).text(allLabel);
      if (!$("#state").val()) {
        $allSt.addClass("is-active");
      }
      $states.append($allSt);

      data.states.forEach(function (s) {
        var active = String($("#state").val()) === String(s.id);
        var $b = $("<button/>", {
          type: "button",
          class: "location-picks__chip" + (active ? " is-active" : ""),
          "data-kind": "state",
          "data-id": s.id,
        }).text(s.name);
        $states.append($b);
      });
      $cities.empty();
      $secCity.hide();
    } else if (data.cities && data.cities.length > 0) {
      $secState.hide();
      $secCity.show();
      data.cities.forEach(function (c) {
        var active = String($("#city").val()) === String(c.id);
        var $b = $("<button/>", {
          type: "button",
          class: "location-picks__chip" + (active ? " is-active" : ""),
          "data-kind": "city",
          "data-id": c.id,
        }).text(c.name);
        $cities.append($b);
      });
    } else {
      $secState.hide();
    }
  }

  function fetchStatesForCountry(countryId) {
    $.ajax({
      type: "GET",
      url: getStateUrl,
      data: { id: countryId },
      success: function (data) {
        renderStatesFromAjax(data);
        var sid = $("#state").val();
        if (sid && data.states && data.states.length) {
          fetchCitiesForState(sid);
        }
      },
      error: function (xhr, status, err) {
        console.error("getStateUrl", status, err);
      },
    });
  }

  function fetchCitiesForState(stateId) {
    if (!stateId) {
      $secCity.hide();
      $cities.empty();
      return;
    }
    $.ajax({
      type: "POST",
      url: getCityUrl,
      data: { id: stateId },
      success: function (data) {
        $cities.empty();
        if (data && data.length > 0) {
          $secCity.show();
          var $allC = $("<button/>", {
            type: "button",
            class: "location-picks__chip location-picks__chip--muted",
            "data-kind": "city",
            "data-all": "true",
          }).text(allLabel);
          if (!$("#city").val()) {
            $allC.addClass("is-active");
          }
          $cities.append($allC);

          data.forEach(function (c) {
            var active = String($("#city").val()) === String(c.id);
            var $b = $("<button/>", {
              type: "button",
              class: "location-picks__chip" + (active ? " is-active" : ""),
              "data-kind": "city",
              "data-id": c.id,
            }).text(c.name);
            $cities.append($b);
          });
        } else {
          $secCity.hide();
        }
      },
      error: function (xhr, status, err) {
        console.error("getCityUrl", status, err);
      },
    });
  }

  function applyCountry(id, name) {
    $("#country").val(id || "");
    $("#state").val("");
    $("#city").val("");
    $("#page").val(1);
    $root.find('.location-picks__chip[data-kind="country"]').removeClass("is-active");
    if (id) {
      $root
        .find('.location-picks__chip[data-kind="country"]')
        .filter(function () {
          return String($(this).attr("data-id")) === String(id);
        })
        .addClass("is-active");
    }
    updateUrl();
    if (!id) {
      $secState.hide();
      $secCity.hide();
      $states.empty();
      $cities.empty();
      return;
    }
    fetchStatesForCountry(id);
  }

  function applyState(id) {
    $("#state").val(id || "");
    $("#city").val("");
    $("#page").val(1);
    $states.find(".location-picks__chip").removeClass("is-active");
    if (id) {
      $states.find('.location-picks__chip[data-kind="state"][data-id="' + id + '"]').addClass("is-active");
    } else {
      $states.find('.location-picks__chip[data-kind="state"][data-all="true"]').addClass("is-active");
    }
    updateUrl();
    fetchCitiesForState(id);
  }

  function applyCity(id) {
    $("#city").val(id || "");
    $("#page").val(1);
    $cities.find(".location-picks__chip").removeClass("is-active");
    if (id) {
      $cities.find('.location-picks__chip[data-kind="city"][data-id="' + id + '"]').addClass("is-active");
    } else {
      $cities.find('.location-picks__chip[data-kind="city"][data-all="true"]').addClass("is-active");
    }
    updateUrl();
  }

  $countrySearch
    .off("input.locationPicks")
    .on(
      "input.locationPicks",
      debounce(function () {
        countrySearchTerm = ($(this).val() || "").trim();
        countryPage = 1;
        loadCountries(true);
      }, 280)
    );

  $btnMore
    .off("click.locationPicks")
    .on("click.locationPicks", function () {
      if (!countryHasMore) {
        return;
      }
      countryPage += 1;
      $.getJSON(lpCountryUrl(), { search: countrySearchTerm, page: countryPage })
        .done(function (data) {
          var results = data.results || [];
          results.forEach(function (r) {
            if ($countries.find('.location-picks__chip[data-id="' + r.id + '"]').length) {
              return;
            }
            var active = String($("#country").val()) === String(r.id);
            var $b = $("<button/>", {
              type: "button",
              class: "location-picks__chip" + (active ? " is-active" : ""),
              "data-id": r.id,
              "data-kind": "country",
            }).text(r.name);
            $countries.append($b);
          });
          countryHasMore = data.more === true;
          $btnMore.toggle(countryHasMore);
        })
        .fail(function (jqXHR, textStatus, err) {
          console.error("[location-picks] get-country (load more) failed:", textStatus, err, jqXHR && jqXHR.status);
        });
    });

  $root
    .off("click.locationPicks")
    .on("click.locationPicks", ".location-picks__chip", function () {
      var kind = $(this).attr("data-kind");
      if ($(this).attr("data-all") === "true") {
        if (kind === "state") {
          applyState("");
        } else if (kind === "city") {
          applyCity("");
        }
        return;
      }
      var rawId = $(this).attr("data-id");
      var id = rawId === undefined || rawId === "" ? "" : rawId;
      if (kind === "country") {
        applyCountry(id, $(this).text());
      } else if (kind === "state") {
        applyState(id);
      } else if (kind === "city") {
        applyCity(id);
      }
    });

  $root
    .off("click.clearLocation")
    .on("click.clearLocation", "[data-location-picks='clear']", function () {
      $("#country").val("");
      $("#state").val("");
      $("#city").val("");
      $("#location_val").val("");
      $("#page").val(1);
      countrySearchTerm = "";
      $countrySearch.val("");
      countryPage = 1;
      $secState.hide();
      $secCity.hide();
      $states.empty();
      $cities.empty();
      loadCountries(true);
      updateUrl();
    });

  countryPage = 1;
  countrySearchTerm = ($countrySearch.val() || "").trim();
  loadCountries(true);

  var cid = $("#country").val();
  if (cid) {
    fetchStatesForCountry(cid);
    if ($("#state").val()) {
      fetchCitiesForState($("#state").val());
    }
  } else {
    $secState.hide();
    $secCity.hide();
  }
};

$(function () {
  if ($("#location-picks").length) {
    initLocationPicks();
  }
});
