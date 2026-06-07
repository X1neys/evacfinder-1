document.addEventListener('DOMContentLoaded', function () {
  if (typeof L === 'undefined') {
    console.error('Leaflet is not loaded');
    return;
  }

  const initialLatLng = [10.6764, 122.9568];

  const mapEl = document.getElementById('homeMap');
  if (!mapEl) return;

  mapEl.style.height = 'calc(100vh - 60px)';
  mapEl.style.zIndex = '0';

  const map = L.map('homeMap', {
    zoomControl: true,
    attributionControl: false,
  }).setView(initialLatLng, 12);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
  }).addTo(map);

  const urlParams = new URLSearchParams(window.location.search);
  const highlightedCenterId = urlParams.get('highlight');
  const centerMarkersById = {};

  function escapeHtml(str) {
    if (!str) return '';
    return str
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function getTypeColor(type) {
    const colors = {
      'Advisory': '#e74c3c',
      'Event':    '#2ecc71',
      'Memo':     '#f39c12',
      'Notice':   '#9b59b6',
      'General':  '#1a3c5e',
    };
    return colors[type] || '#1a3c5e';
  }

  // ── Announcements ─────────────────────────────────────────
  function loadAnnouncements() {
    fetch('ajax/get_announcements.ajax.php')
      .then(res => {
        if (!res.ok) throw new Error('Network response was not ok');
        return res.json();
      })
      .then(data => {
        const list  = document.getElementById('announcementsList');
        const badge = document.getElementById('announcementBadge');
        if (!list) return;

        if (badge && data.length > 0) {
          badge.textContent    = data.length;
          badge.style.display  = 'flex';
        } else if (badge) {
          badge.style.display  = 'none';
        }

        if (!data.length) {
          list.innerHTML = '<div style="text-align:center;padding:30px;color:#888;font-size:13px;">📭 No announcements available.</div>';
          return;
        }

        list.innerHTML = data.map((ann, index) => {
          const color = getTypeColor(ann.ann_type);
          return `
            <div class="announcement-item"
              data-id="${escapeHtml(ann.announcement_id || index)}"
              style="background:#f8f9fa;border-radius:8px;padding:12px 14px;margin-bottom:12px;border-left:4px solid ${color};cursor:pointer;transition:box-shadow 0.2s;"
              onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,0.12)'"
              onmouseout="this.style.boxShadow='none'"
            >
              <span style="display:inline-block;font-size:11px;background:${color}22;color:${color};border-radius:4px;padding:2px 8px;font-weight:600;margin-bottom:6px;">
                ${escapeHtml(ann.ann_type)}
              </span>
              <div style="font-weight:600;font-size:13px;margin-bottom:4px;color:#1a3c5e;">${escapeHtml(ann.ann_title)}</div>
              <div style="font-size:12px;color:#555;margin-bottom:6px;line-height:1.5;">${escapeHtml(ann.ann_desc)}</div>
              <div style="font-size:11px;color:#aaa;">${ann.date_created || ''}</div>
            </div>
          `;
        }).join('');
      })
      .catch(err => {
        console.error('Failed to load announcements:', err);
        const list = document.getElementById('announcementsList');
        if (list) list.innerHTML = '<div style="text-align:center;padding:30px;color:#e74c3c;font-size:13px;">⚠️ Failed to load announcements.</div>';
      });
  }

  function initAnnouncementsPanel() {
    const panel   = document.getElementById('announcementsPanel');
    const showBtn = document.getElementById('showAnnouncementsBtn');
    if (!panel || !showBtn) return;

    showBtn.addEventListener('click', function () {
      const isOpen = panel.style.display === 'flex';
      panel.style.display = isOpen ? 'none' : 'flex';
      if (!isOpen) loadAnnouncements();
    });
  }

  // ── Center markers with clustering ────────────────────────
  function loadCenterMarkers() {
    if (typeof L.markerClusterGroup === 'undefined') {
      console.error('MarkerCluster plugin not loaded');
      return;
    }

    const clusterGroup = L.markerClusterGroup({
      iconCreateFunction: function (cluster) {
        const count = cluster.getChildCount();
        return L.divIcon({
          className: '',
          html: `
            <svg width="46" height="46" viewBox="0 0 46 46" xmlns="http://www.w3.org/2000/svg">
              <circle cx="23" cy="23" r="21" fill="#1a3c5e" stroke="white" stroke-width="3"/>
              <text x="23" y="28" text-anchor="middle" font-size="14" font-weight="bold" fill="white">${count}</text>
            </svg>
          `,
          iconSize: [46, 46],
          iconAnchor: [23, 23],
        });
      },
      spiderfyOnMaxZoom: true,
      showCoverageOnHover: false,
      zoomToBoundsOnClick: true,
      maxClusterRadius: 60,
    });

    fetch('ajax/get_available_centers.ajax.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: ''
    })
      .then(res => res.json())
      .then(data => {
        if (!data.success || !data.centers) return;

        data.centers.forEach(center => {
          const lat = parseFloat(center.latitude);
          const lng = parseFloat(center.longitude);
          if (isNaN(lat) || isNaN(lng)) return;

          const occupancy = center.capacity > 0
            ? Math.round((center.current_occupants / center.capacity) * 100)
            : 0;

          const markerColor = occupancy >= 100 ? '#e74c3c'
                            : occupancy >= 75  ? '#f39c12'
                            : '#2ecc71';

          const icon = L.divIcon({
            className: '',
            html: `
              <svg width="46" height="56" viewBox="0 0 46 56" xmlns="http://www.w3.org/2000/svg">
                <circle cx="23" cy="23" r="21" fill="${markerColor}" stroke="white" stroke-width="3"/>
                <polygon points="23,8 10,20 36,20" fill="white"/>
                <rect x="13" y="20" width="20" height="14" fill="white"/>
                <rect x="19" y="26" width="8" height="8" fill="${markerColor}"/>
                <polygon points="16,42 23,56 30,42" fill="${markerColor}"/>
              </svg>
            `,
            iconSize: [46, 56],
            iconAnchor: [23, 56],
            popupAnchor: [0, -58],
          });

          const marker = L.marker([lat, lng], { icon })
            .bindPopup(`
              <strong>${escapeHtml(center.center_name)}</strong><br>
              Occupancy: ${center.current_occupants} / ${center.capacity}<br>
              <span style="color:${markerColor};font-weight:600;">${occupancy}% full</span>
            `);

          if (center.center_id) {
            centerMarkersById[center.center_id] = marker;
          }

          clusterGroup.addLayer(marker);
        });

        map.addLayer(clusterGroup);

        if (highlightedCenterId && centerMarkersById[highlightedCenterId]) {
          const highlightedMarker = centerMarkersById[highlightedCenterId];
          highlightedMarker.openPopup();
          map.setView(highlightedMarker.getLatLng(), 15);
        }
      })
      .catch(err => console.error('Failed to load center markers:', err));
  }

  // ── Routing to Nearest Center ─────────────────────────────
  let routingControl = null;
  let userLocationMarker = null;
  let isRoutingActive = false;

  function haversineDistance(lat1, lng1, lat2, lng2) {
    const R = 6371; // km
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2)
            + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180)
            * Math.sin(dLng / 2) * Math.sin(dLng / 2);
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  }

  function setRouteButtonState(state) {
    const btn = document.getElementById('routeToNearestBtn');
    const label = document.getElementById('routeBtnLabel');
    if (!btn || !label) return;

    if (state === 'loading') {
      btn.disabled = true;
      label.textContent = 'Finding route…';
      btn.style.background = '#6c757d';
    } else if (state === 'active') {
      btn.disabled = false;
      label.textContent = 'Clear Route';
      btn.style.background = '#e74c3c';
    } else {
      btn.disabled = false;
      label.textContent = 'Route to Nearest';
      btn.style.background = '#1a3c5e';
    }
  }

  function clearRoute() {
    if (routingControl) {
      map.removeControl(routingControl);
      routingControl = null;
    }
    if (userLocationMarker) {
      map.removeLayer(userLocationMarker);
      userLocationMarker = null;
    }
    const infoPanel = document.getElementById('routeInfoPanel');
    if (infoPanel) infoPanel.style.display = 'none';
    isRoutingActive = false;
    setRouteButtonState('idle');
  }

  function showRouteInfo(centerName, distanceKm) {
    const infoPanel = document.getElementById('routeInfoPanel');
    if (!infoPanel) return;
    const km = distanceKm.toFixed(2);
    infoPanel.innerHTML = `
      <div style="display:flex;align-items:center;gap:8px;">
        <span style="font-size:18px;">🧭</span>
        <div>
          <div style="font-weight:700;font-size:13px;color:#1a3c5e;">${escapeHtml(centerName)}</div>
          <div style="font-size:12px;color:#555;">~${km} km away (straight line)</div>
        </div>
        <button onclick="document.getElementById('routeToNearestBtn').click()"
          style="margin-left:auto;background:none;border:none;cursor:pointer;font-size:18px;color:#aaa;line-height:1;" title="Clear route">✕</button>
      </div>
    `;
    infoPanel.style.display = 'block';
  }

  function routeToNearest() {
    if (isRoutingActive) {
      clearRoute();
      return;
    }

    if (!navigator.geolocation) {
      alert('Geolocation is not supported by your browser.');
      return;
    }

    setRouteButtonState('loading');

    navigator.geolocation.getCurrentPosition(
      function (position) {
        const userLat = position.coords.latitude;
        const userLng = position.coords.longitude;

        // Fetch active centers then find nearest
        fetch('ajax/get_available_centers.ajax.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: ''
        })
          .then(res => res.json())
          .then(data => {
            if (!data.success || !data.centers || data.centers.length === 0) {
              alert('No active evacuation centers found.');
              setRouteButtonState('idle');
              return;
            }

            // Find nearest center by straight-line distance
            let nearest = null;
            let nearestDist = Infinity;

            data.centers.forEach(center => {
              const lat = parseFloat(center.latitude);
              const lng = parseFloat(center.longitude);
              if (isNaN(lat) || isNaN(lng)) return;
              const dist = haversineDistance(userLat, userLng, lat, lng);
              if (dist < nearestDist) {
                nearestDist = dist;
                nearest = { ...center, lat, lng };
              }
            });

            if (!nearest) {
              alert('Could not find a valid center with coordinates.');
              setRouteButtonState('idle');
              return;
            }

            // Place user location marker
            const userIcon = L.divIcon({
              className: '',
              html: `
                <svg width="28" height="28" viewBox="0 0 28 28" xmlns="http://www.w3.org/2000/svg">
                  <circle cx="14" cy="14" r="12" fill="#2563eb" stroke="white" stroke-width="3"/>
                  <circle cx="14" cy="14" r="5" fill="white"/>
                </svg>
              `,
              iconSize: [28, 28],
              iconAnchor: [14, 14],
            });

            userLocationMarker = L.marker([userLat, userLng], { icon: userIcon })
              .addTo(map);

            // Draw route using OSRM (free, no API key needed)
            if (typeof L.Routing === 'undefined') {
              alert('Routing plugin not loaded. Make sure leaflet-routing-machine is included.');
              setRouteButtonState('idle');
              return;
            }

            routingControl = L.Routing.control({
              waypoints: [
                L.latLng(userLat, userLng),
                L.latLng(nearest.lat, nearest.lng),
              ],
              router: L.Routing.osrmv1({
                serviceUrl: 'https://router.project-osrm.org/route/v1',
              }),
              lineOptions: {
                styles: [{ color: '#1a3c5e', weight: 5, opacity: 0.8 }],
                extendToWaypoints: true,
                missingRouteTolerance: 0,
              },
              createMarker: function () { return null; }, // suppress default markers
              show: false,       // hide the turn-by-turn instructions panel
              addWaypoints: false,
              draggableWaypoints: false,
              fitSelectedRoutes: true,
              showAlternatives: false,
            }).addTo(map);

            isRoutingActive = true;
            setRouteButtonState('active');
            showRouteInfo(nearest.center_name, nearestDist);
          })
          .catch(err => {
            console.error('Routing error:', err);
            alert('Failed to load center data for routing.');
            setRouteButtonState('idle');
          });
      },
      function (err) {
        console.error('Geolocation error:', err);
        alert('Unable to get your location. Please allow location access and try again.');
        setRouteButtonState('idle');
      },
      { enableHighAccuracy: true, timeout: 10000 }
    );
  }

  function initRouting() {
    const btn = document.getElementById('routeToNearestBtn');
    if (!btn) return;
    btn.addEventListener('click', routeToNearest);
  }

  // ── Init ──────────────────────────────────────────────────
  initAnnouncementsPanel();
  loadAnnouncements();
  loadCenterMarkers();
  initRouting();
  setInterval(loadAnnouncements, 30000);
});