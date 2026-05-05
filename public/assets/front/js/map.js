"use strict";
var markers = [],
  map, marker_clusterer;

/** PHP json_encode of Collections can emit JSON objects; Leaflet needs arrays of rows. */
function normalizeMapRows(data) {
  if (data == null) {
    return [];
  }
  if (Array.isArray(data)) {
    return data;
  }
  if (typeof data === 'object') {
    return Object.values(data);
  }
  return [];
}

$(document).ready(function () {
  var featured_content = normalizeMapRows(typeof featured_contents !== 'undefined' ? featured_contents : []);
  var listing_content;
  if (
    typeof listing_contents === 'object' &&
    listing_contents !== null &&
    typeof listing_contents.current_page === 'number' &&
    listing_contents.data
  ) {
    listing_content = normalizeMapRows(listing_contents.data);
  } else {
    listing_content = normalizeMapRows(typeof listing_contents !== 'undefined' ? listing_contents : []);
  }

  var mapId = $(".btn[data-bs-target='#mapModal']").is(":visible") ? "modal-main-map" : "main-map";

  mapInitialize(mapId, featured_content, listing_content);

  var mapModal = document.getElementById('mapModal');
  if (mapModal) {
    mapModal.addEventListener('shown.bs.modal', function () {
      if (map && typeof map.invalidateSize === 'function') {
        map.invalidateSize();
      }
    });
  }

  $(window).on('load', function () {
    if (map && typeof map.invalidateSize === 'function') {
      map.invalidateSize();
    }
  });
  var resizeT;
  $(window).on('resize orientationchange', function () {
    clearTimeout(resizeT);
    resizeT = setTimeout(function () {
      if (map && typeof map.invalidateSize === 'function') {
        map.invalidateSize();
      }
    }, 150);
  });
});
var timerMap, ad_galleries, firstSet = !1,
  mapRefresh = !0,
  loadOnTab = !0,
  zoomOnMapSearch = 22,
  clusterConfig = null,
  markerOptions = null,
  mapDisableAutoPan = !1,
  rent_inc_id = '55',
  scrollWheelEnabled = !1,
  myLocationEnabled = !0,
  rectangleSearchEnabled = !0,
  mapSearchbox = !0,
  mapRefresh = !0,
  map_main, styles, mapStyle = [{
    'featureType': 'landscape',
    'elementType': 'geometry.fill',
    'stylers': [{
      'color': '#fcf4dc'
    }]
  }, {
    'featureType': 'landscape',
    'elementType': 'geometry.stroke',
    'stylers': [{
      'color': '#c0c0c0'
    }, {
      'visibility': 'on'
    }]
  }];

var clusters = L.markerClusterGroup({
  spiderfyOnMaxZoom: !0,
  showCoverageOnHover: !1,
  zoomToBoundsOnClick: !0
});

var jpopup_customOptions = {
  'maxWidth': 'initial',
  'width': 'initial',
  'className': 'popupCustom'
};

function mapInitialize(mapId, featured_content, listing_content) {
  var container = document.getElementById(mapId);
  if (!container) {
    return;
  }

  if (map && typeof map.remove === 'function') {
    try {
      map.remove();
    } catch (err) { /* ignore */ }
    map = null;
  }

  markers = [];
  clusters = L.markerClusterGroup({
    spiderfyOnMaxZoom: true,
    showCoverageOnHover: false,
    zoomToBoundsOnClick: true
  });

  var o = true;
  map = L.map(mapId, {
    center: [20, 0],
    zoom: 2,
    minZoom: 0,
    maxZoom: 22,
    scrollWheelZoom: o,
    tap: !L.Browser.mobile,
    fullscreenControl: true,
    fullscreenControlOptions: {
      position: 'topleft'
    }
  });

  L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png', {
    maxZoom: 22
  }).addTo(map);

  function validCoord(lat, lng) {
    var la = parseFloat(lat);
    var ln = parseFloat(lng);
    if (!isFinite(la) || !isFinite(ln)) {
      return null;
    }
    if (la < -90 || la > 90 || ln < -180 || ln > 180) {
      return null;
    }
    return [la, ln];
  }

  function addMarkers(rows) {
    rows = normalizeMapRows(rows);
    rows.forEach(function (element) {
      if (!element) {
        return;
      }
      var coords = validCoord(element.latitude, element.longitude);
      if (!coords) {
        return;
      }

      var cityId = element.city_id;
      var stateId = element.state_id;
      var countryId = element.country_id;

      var a = L.marker(coords, {
        icon: L.divIcon({
          html: '<div class="marker-container"><div class="marker-card"><div class="front face"><i class="' + (element.icon || '') + '"></i></div><div class="marker-arrow"></div></div></div>',
          className: 'open_street_map_marker google_marker',
          iconSize: [40, 46],
          popupAnchor: [1, -35],
          iconAnchor: [20, 46],
        })
      });

      var imgBase = (typeof baseURL !== 'undefined' ? baseURL : '') + '/assets/img/listing/';
      $.ajax({
        url: baseURL + '/listings/get-address',
        method: 'GET',
        data: {
          city_id: cityId,
          state_id: stateId,
          country_id: countryId
        },
        success: function (response) {
          var address = response || '';
          a.bindPopup(
            '<div class="product-default p-0"> <figure class="product-img"> <a href="listings/' + element.slug + '/' + element.id + '" class="lazy-container ratio ratio-2-3"> <img class="lazyload" src="' + imgBase + element.feature_image + '" data-src="' + imgBase + element.feature_image + '" alt="Product"> </a></figure><div class="product-details"><h6 class="product-title"><a href="listings/' + element.slug + '/' + element.id + '">' + element.title + '</a></h6><span class="product-location icon-start"><i class="fal fa-map-marker-alt"></i>' + address + '</span></div></div>',
            jpopup_customOptions
          );
        },
        error: function (xhr, status, error) {
          console.error(xhr, status, error);
        }
      });

      clusters.addLayer(a);
      markers.push(a);
    });
  }

  addMarkers(featured_content);
  addMarkers(listing_content);

  map.addLayer(clusters);

  if (markers.length) {
    var latLngs = [];
    for (var i = 0; i < markers.length; i++) {
      if (markers[i] && typeof markers[i].getLatLng === 'function') {
        latLngs.push(markers[i].getLatLng());
      }
    }
    if (latLngs.length === 1) {
      map.setView(latLngs[0], 13);
    } else if (latLngs.length > 1) {
      map.fitBounds(L.latLngBounds(latLngs), { padding: [40, 40] });
    }
  }

  requestAnimationFrame(function () {
    if (map && typeof map.invalidateSize === 'function') {
      map.invalidateSize();
    }
  });
  setTimeout(function () {
    if (map && typeof map.invalidateSize === 'function') {
      map.invalidateSize();
    }
  }, 200);
}
