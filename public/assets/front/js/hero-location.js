"use strict";

/**
 * Hero home search: country (Select2 ajax) → state → city, matching listings filter behaviour.
 * Expects window.countryUrl, getStateUrl, getCityUrl set in the blade before this file loads.
 */
$(document).ready(function () {
  var $country = $("#hero_country");
  if (!$country.length) {
    return;
  }

  var selectCountry =
    typeof window.heroSelectCountry === "string" ? window.heroSelectCountry : "Country";
  var selectState =
    typeof window.heroSelectState === "string" ? window.heroSelectState : "State / region";
  var selectCity =
    typeof window.heroSelectCity === "string" ? window.heroSelectCity : "City";

  var countryAjaxUrl = window.countryUrl || (typeof countryUrl !== "undefined" ? countryUrl : "");
  var stateUrl = window.getStateUrl || "";
  var cityUrl = window.getCityUrl || "";

  if (!countryAjaxUrl || !stateUrl || !cityUrl) {
    console.warn("[hero-location] Missing country/state/city URLs");
    return;
  }

  var $stateWrap = $(".hero-state-wrap");
  var $cityWrap = $(".hero-city-wrap");
  var $state = $("#hero_state");
  var $city = $("#hero_city");

  function resetStateCity() {
    $state.empty().append($("<option></option>").val("").text(selectState));
    $city.empty().append($("<option></option>").val("").text(selectCity));
    $stateWrap.hide();
    $cityWrap.hide();
  }

  if ($country.data("select2")) {
    $country.select2("destroy");
  }

  $country.select2({
    placeholder: selectCountry,
    allowClear: true,
    width: "100%",
    dropdownCssClass: "select2-dropdown--hero-categories",
    ajax: {
      url: countryAjaxUrl,
      dataType: "json",
      delay: 250,
      data: function (params) {
        return { search: params.term || "", page: params.page || 1 };
      },
      processResults: function (data) {
        return {
          results: (data.results || []).map(function (item) {
            return { text: item.name, id: item.id };
          }),
          pagination: { more: data.more === true },
        };
      },
      cache: true,
    },
  });

  $country.on("change", function () {
    var id = $(this).val();
    resetStateCity();
    if (!id) {
      return;
    }

    $.ajax({
      type: "GET",
      url: stateUrl,
      data: { id: id },
      success: function (data) {
        if (!data || (!data.states && !data.cities)) {
          return;
        }
        if (data.states && data.states.length > 0) {
          $stateWrap.show();
          $cityWrap.hide();
          $state.empty();
          $state.append($("<option></option>").val("").text(selectState));
          $.each(data.states, function (_k, value) {
            $state.append($("<option></option>").val(value.id).text(value.name));
          });
          $city.empty().append($("<option></option>").val("").text(selectCity));
        } else if (data.cities && data.cities.length > 0) {
          $stateWrap.hide();
          $cityWrap.show();
          $city.empty();
          $city.append($("<option></option>").val("").text(selectCity));
          $.each(data.cities, function (_k, value) {
            $city.append($("<option></option>").val(value.id).text(value.name));
          });
        }
      },
      error: function (xhr, status, err) {
        console.error("[hero-location] get states failed", status, err);
      },
    });
  });

  $state.on("change", function () {
    var selectedStateId = $(this).val();
    $city.empty().append($("<option></option>").val("").text(selectCity));
    if (!selectedStateId) {
      $cityWrap.hide();
      return;
    }

    $.ajax({
      type: "POST",
      url: cityUrl,
      data: {
        _token: $('meta[name="csrf-token"]').attr("content"),
        id: selectedStateId,
      },
      success: function (data) {
        if (data && data.length > 0) {
          $cityWrap.show();
          $.each(data, function (_k, value) {
            $city.append($("<option></option>").val(value.id).text(value.name));
          });
        } else {
          $cityWrap.hide();
        }
      },
      error: function (xhr, status, err) {
        console.error("[hero-location] get cities failed", status, err);
      },
    });
  });
});
