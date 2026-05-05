"use strict";

(function () {
  var $modal = $("#askAiModal");
  if (!$modal.length) {
    return;
  }

  var url = window.askAiUrl || "";
  var locationInitialized = false;

  var $title = $("#askAiTitle");
  var $country = $("#ask_ai_country");
  var $state = $("#ask_ai_state");
  var $city = $("#ask_ai_city");
  var $stateWrap = $modal.find(".ask-ai-state-wrap");
  var $cityWrap = $modal.find(".ask-ai-city-wrap");

  var $searchBtn = $("#askAiSearchBtn");
  var $nextBtn = $("#askAiNext");
  var $backBtn = $("#askAiBack");
  var $panel1 = $modal.find('[data-ask-panel="1"]');
  var $panel2 = $modal.find('[data-ask-panel="2"]');
  var $pill1 = $("#askAiStepPill1");
  var $pill2 = $("#askAiStepPill2");
  var $subStep1 = $("#askAiSubStep1");
  var $subStep2 = $("#askAiSubStep2");
  var $err = $("#askAiError");
  var $wrap = $("#askAiResultsWrap");
  var $list = $("#askAiResultsList");
  var $expl = $("#askAiExplanation");
  var $viewAll = $("#askAiViewAll");
  var $viewAllWrap = $("#askAiViewAllWrap");

  var currentStep = 1;

  var selectCountry =
    typeof window.heroSelectCountry === "string" ? window.heroSelectCountry : "Country";
  var selectState =
    typeof window.heroSelectState === "string" ? window.heroSelectState : "State / region";
  var selectCity =
    typeof window.heroSelectCity === "string" ? window.heroSelectCity : "City";

  function showError(msg) {
    $err.text(msg).removeClass("d-none");
  }

  function hideError() {
    $err.addClass("d-none").text("");
  }

  function getSelectedCategorySlug() {
    var $active = $modal.find(".ask-ai-cat-btn.is-active");
    if (!$active.length) {
      return "";
    }
    var slug = $active.attr("data-slug");
    return slug === undefined || slug === null ? "" : String(slug);
  }

  function setStep(step) {
    currentStep = step === 2 ? 2 : 1;

    $panel1.toggleClass("d-none", currentStep !== 1);
    $panel2.toggleClass("d-none", currentStep !== 2);
    $panel1.toggleClass("ask-ai-step-panel--active", currentStep === 1);
    $panel2.toggleClass("ask-ai-step-panel--active", currentStep === 2);

    $pill1.toggleClass("is-active", currentStep === 1);
    $pill2.toggleClass("is-active", currentStep === 2);

    if ($subStep1.length && $subStep2.length) {
      $subStep1.toggleClass("d-none", currentStep !== 1);
      $subStep2.toggleClass("d-none", currentStep !== 2);
    }

    $backBtn.toggleClass("d-none", currentStep === 1);
    $nextBtn.toggleClass("d-none", currentStep === 2);
    $searchBtn.toggleClass("d-none", currentStep === 1);

    if (currentStep === 2 && !locationInitialized) {
      initAskAiLocation();
    }
  }

  function resetModal() {
    $title.val("");
    $modal.find(".ask-ai-cat-btn").removeClass("is-active");
    $modal.find(".ask-ai-cat-btn--all").addClass("is-active");

    if ($country.data("select2")) {
      $country.val(null).trigger("change");
    } else {
      $country.val("");
    }
    $state.empty().append($("<option></option>").val("").text(selectState));
    $city.empty().append($("<option></option>").val("").text(selectCity));
    $stateWrap.hide();
    $cityWrap.hide();
    hideError();
    $wrap.addClass("d-none");
    $list.empty();
    setLoading(false);
    setStep(1);
  }

  function initAskAiLocation() {
    var countryAjaxUrl = window.countryUrl || "";
    var stateUrl = window.getStateUrl || "";
    var cityUrl = window.getCityUrl || "";
    if (!countryAjaxUrl || !stateUrl || !cityUrl) {
      return;
    }

    if ($country.data("select2")) {
      $country.select2("destroy");
    }

    $country.select2({
      placeholder: selectCountry,
      allowClear: true,
      width: "100%",
      dropdownParent: $modal,
      dropdownCssClass: "select2-dropdown--ask-ai",
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

    function resetStateCity() {
      $state.empty().append($("<option></option>").val("").text(selectState));
      $city.empty().append($("<option></option>").val("").text(selectCity));
      $stateWrap.hide();
      $cityWrap.hide();
    }

    $country.off("change.askAi").on("change.askAi", function () {
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
      });
    });

    $state.off("change.askAi").on("change.askAi", function () {
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
      });
    });

    locationInitialized = true;
  }

  function setLoading(on) {
    if (on) {
      $searchBtn.prop("disabled", true);
      $searchBtn.find(".ask-ai-submit-label").addClass("d-none");
      $searchBtn.find(".ask-ai-submit-loading").removeClass("d-none");
    } else {
      $searchBtn.prop("disabled", false);
      $searchBtn.find(".ask-ai-submit-label").removeClass("d-none");
      $searchBtn.find(".ask-ai-submit-loading").addClass("d-none");
    }
  }

  function escapeHtml(s) {
    var d = document.createElement("div");
    d.textContent = s;
    return d.innerHTML;
  }

  function renderResults(data) {
    $expl.text(data.explanation || "");
    $list.empty();
    if (data.listings && data.listings.length) {
      data.listings.forEach(function (item) {
        var li = document.createElement("li");
        li.className = "ask-ai-result-item mb-2";
        li.innerHTML =
          '<a class="ask-ai-result-link d-block p-3 rounded-3 text-decoration-none" href="' +
          item.url +
          '">' +
          '<span class="d-block fw-semibold color-dark">' +
          escapeHtml(item.title) +
          "</span>" +
          '<span class="d-block small text-muted mt-1">' +
          escapeHtml(item.category_name || "") +
          "</span>" +
          "</a>";
        $list.append(li);
      });
    } else {
      $list.append(
        '<li class="text-muted small py-2">' +
          (window.askAiNoResults || "No matches — try different filters.") +
          "</li>"
      );
    }
    if (data.view_all_url) {
      $viewAll.attr("href", data.view_all_url);
      $viewAllWrap.removeClass("d-none");
    } else {
      $viewAllWrap.addClass("d-none");
    }
    $wrap.removeClass("d-none");
  }

  function runSearch() {
    var cat = getSelectedCategorySlug();
    var payload = {
      title: ($title.val() || "").trim(),
      category_id: cat,
      country: ($country.val() || "").trim(),
      state: ($state.val() || "").trim(),
      city: ($city.val() || "").trim(),
    };

    if (
      !payload.title &&
      !payload.category_id &&
      !payload.country &&
      !payload.state &&
      !payload.city
    ) {
      showError(
        window.askAiNeedFilter ||
          "Please add keywords, pick a category, or choose a place."
      );
      return;
    }

    hideError();
    setLoading(true);
    $wrap.addClass("d-none");

    $.ajax({
      url: url,
      method: "POST",
      data: JSON.stringify(payload),
      contentType: "application/json",
      headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        Accept: "application/json",
      },
    })
      .done(function (res) {
        if (res.ok === false) {
          showError(res.message || "Something went wrong.");
          return;
        }
        renderResults(res);
      })
      .fail(function (xhr) {
        var msg = "Request failed.";
        var j = xhr.responseJSON;
        if (j && j.message) {
          msg = j.message;
        }
        showError(msg);
      })
      .always(function () {
        setLoading(false);
      });
  }

  $modal.on("click", ".ask-ai-cat-btn", function () {
    $modal.find(".ask-ai-cat-btn").removeClass("is-active");
    $(this).addClass("is-active");
    setStep(2);
  });

  $searchBtn.on("click", function () {
    runSearch();
  });

  $nextBtn.on("click", function () {
    setStep(2);
  });

  $backBtn.on("click", function () {
    setStep(1);
  });

  $modal.on("click", "[data-go-step]", function () {
    var s = parseInt($(this).attr("data-go-step"), 10);
    if (s === 2) {
      setStep(2);
    } else {
      setStep(1);
    }
  });

  $modal.on("hidden.bs.modal", function () {
    document.body.classList.remove("ask-ai-modal-open");
    document.querySelectorAll(".modal-backdrop.ask-ai-modal-backdrop").forEach(function (el) {
      el.classList.remove("ask-ai-modal-backdrop");
    });
    resetModal();
  });

  $modal.on("shown.bs.modal", function () {
    document.body.classList.add("ask-ai-modal-open");
    function tagBackdrop() {
      var b = document.querySelector(".modal-backdrop.show");
      if (b) {
        b.classList.add("ask-ai-modal-backdrop");
      }
    }
    tagBackdrop();
    window.requestAnimationFrame(tagBackdrop);
    setStep(1);
  });
})();
