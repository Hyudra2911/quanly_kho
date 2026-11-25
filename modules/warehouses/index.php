<?php
$page_title = 'Quản lý kho hàng';
require_once '../../config/config.php';
checkLogin();

$db = getDB();
$stmt = $db->query("SELECT w.*, u.full_name as manager_name 
    FROM warehouses w 
    LEFT JOIN users u ON w.manager_id = u.id 
    ORDER BY w.name");
$warehouses = $stmt->fetchAll();

// Lấy danh sách quản lý
$stmt = $db->query("SELECT * FROM users WHERE role IN ('admin', 'quanly_kho') ORDER BY full_name");
$managers = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="page-header">
        <h1><i class="bi bi-building"></i> Quản lý kho hàng</h1>
    </div>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">Danh sách kho</h5>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="bi bi-plus-circle"></i> Thêm mới
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Mã kho</th>
                            <th>Tên kho</th>
                            <th>Địa chỉ</th>
                            <th>Quản lý</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($warehouses as $wh): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($wh['code']); ?></td>
                            <td><?php echo htmlspecialchars($wh['name']); ?></td>
                            <td><?php echo htmlspecialchars($wh['address'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($wh['manager_name'] ?? 'N/A'); ?></td>
                            <td>
                                <?php if ($wh['status']): ?>
                                    <span class="badge bg-success">Hoạt động</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Ngừng</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($wh['address']) || ($wh['latitude'] && $wh['longitude'])): ?>
                                <button
                                    class="btn btn-sm btn-success"
                                    onclick="openOnGoogleMaps(
                                        '<?php echo htmlspecialchars($wh['address'] ?? '', ENT_QUOTES); ?>',
                                        <?php echo $wh['latitude'] !== null ? (float)$wh['latitude'] : 'null'; ?>,
                                        <?php echo $wh['longitude'] !== null ? (float)$wh['longitude'] : 'null'; ?>
                                    )"
                                >
                                    <i class="bi bi-geo-alt"></i> Xem bản đồ
                                </button>
                                <?php endif; ?>
                                <a href="view.php?id=<?php echo $wh['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button class="btn btn-sm btn-warning" onclick="editWarehouse(<?php echo htmlspecialchars(json_encode($wh)); ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="delete.php" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa kho này?');">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="id" value="<?php echo $wh['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="save.php">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm kho hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Mã kho <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="code" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tên kho <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Địa chỉ</label>
                        <textarea class="form-control" name="address" id="add_address" rows="2" placeholder="Nhập địa chỉ tìm được trên Google Maps"></textarea>
                        <input type="hidden" name="latitude" id="add_latitude">
                        <input type="hidden" name="longitude" id="add_longitude">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quản lý</label>
                        <select class="form-select" name="manager_id">
                            <option value="">-- Chọn quản lý --</option>
                            <?php foreach ($managers as $mgr): ?>
                            <option value="<?php echo $mgr['id']; ?>"><?php echo htmlspecialchars($mgr['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="save.php">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa kho hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Mã kho <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="code" id="edit_code" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tên kho <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" id="edit_name" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Địa chỉ</label>
                        <textarea class="form-control" name="address" id="edit_address" rows="2" placeholder="Nhập địa chỉ tìm được trên Google Maps"></textarea>
                        <input type="hidden" name="latitude" id="edit_latitude">
                        <input type="hidden" name="longitude" id="edit_longitude">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quản lý</label>
                        <select class="form-select" name="manager_id" id="edit_manager_id">
                            <option value="">-- Chọn quản lý --</option>
                            <?php foreach ($managers as $mgr): ?>
                            <option value="<?php echo $mgr['id']; ?>"><?php echo htmlspecialchars($mgr['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="status" id="edit_status" value="1">
                            <label class="form-check-label" for="edit_status">Hoạt động</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Map Picker Modal -->
<div class="modal fade" id="mapModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chọn vị trí kho</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if (!empty(GOOGLE_MAPS_API_KEY)): ?>
                <div class="mb-2">
                    <input type="text" id="mapSearch" class="form-control" placeholder="Tìm địa điểm...">
                </div>
                <div id="mapContainer" style="height: 450px; width: 100%;"></div>
                <div class="mt-2 text-muted small">Nhấn lên bản đồ để chọn. Tự động điền lat/lng vào form.</div>
                <?php else: ?>
                <div class="alert alert-warning mb-0">
                    Chưa cấu hình GOOGLE_MAPS_API_KEY. Vui lòng đặt biến môi trường hoặc cập nhật trong <code>config/config.php</code>.
                </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Map View Modal (chỉ xem, không chỉnh sửa) -->
<div class="modal fade" id="mapViewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Vị trí kho</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if (!empty(GOOGLE_MAPS_API_KEY)): ?>
                <div class="mb-2">
                    <p class="mb-0"><strong>Địa chỉ:</strong> <span id="viewAddress"></span></p>
                </div>
                <div id="mapViewContainer" style="height: 450px; width: 100%;"></div>
                <?php else: ?>
                <div class="alert alert-warning mb-0">
                    Chưa cấu hình GOOGLE_MAPS_API_KEY. Vui lòng đặt biến môi trường hoặc cập nhật trong <code>config/config.php</code>.
                </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
function editWarehouse(wh) {
    document.getElementById('edit_id').value = wh.id;
    document.getElementById('edit_code').value = wh.code;
    document.getElementById('edit_name').value = wh.name;
    document.getElementById('edit_address').value = wh.address || '';
    document.getElementById('edit_manager_id').value = wh.manager_id || '';
    document.getElementById('edit_status').checked = wh.status == 1;
    document.getElementById('edit_latitude').value = wh.latitude || '';
    document.getElementById('edit_longitude').value = wh.longitude || '';
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

<?php if (!empty(GOOGLE_MAPS_API_KEY)): ?>
let map, marker, autocomplete;
let mapView, markerView;
let targetLatInput = null;
let targetLngInput = null;
let targetAddressInput = null;
let geocoder;

function initMapPicker() {
    const mapEl = document.getElementById('mapContainer');
    if (!mapEl) return;
    map = new google.maps.Map(mapEl, {
        center: { lat: 21.0278, lng: 105.8342 }, // Hà Nội
        zoom: 12,
    });

    geocoder = new google.maps.Geocoder();

    autocomplete = new google.maps.places.Autocomplete(document.getElementById('mapSearch'));
    autocomplete.bindTo('bounds', map);
    autocomplete.addListener('place_changed', () => {
        const place = autocomplete.getPlace();
        if (!place.geometry) return;
        map.panTo(place.geometry.location);
        map.setZoom(15);
        placeMarker(place.geometry.location);
    });

    map.addListener('click', (e) => {
        placeMarker(e.latLng);
        updateAddressFromLatLng(e.latLng);
    });
}

function placeMarker(latLng) {
    if (!marker) {
        marker = new google.maps.Marker({
            position: latLng,
            map,
        });
    } else {
        marker.setPosition(latLng);
    }
    if (targetLatInput && targetLngInput) {
        targetLatInput.value = latLng.lat().toFixed(7);
        targetLngInput.value = latLng.lng().toFixed(7);
    }
}

function updateAddressFromLatLng(latLng) {
    if (!geocoder || !targetAddressInput) return;
    geocoder.geocode({ location: latLng }, (results, status) => {
        if (status === 'OK' && results && results[0]) {
            targetAddressInput.value = results[0].formatted_address;
        }
    });
}

function geocodeAddress(address, onDone) {
    if (!geocoder) return;
    geocoder.geocode({ address }, (results, status) => {
        if (status === 'OK' && results && results[0]) {
            const loc = results[0].geometry.location;
            onDone(loc);
        } else {
            alert('Không tìm thấy địa chỉ trên bản đồ. Vui lòng kiểm tra lại.');
        }
    });
}

function openMapPicker(latInput, lngInput, addressInput) {
    if (!(window.google && window.google.maps)) {
        alert('Bản đồ đang tải, vui lòng thử lại sau giây lát.');
        return;
    }
    targetLatInput = latInput;
    targetLngInput = lngInput;
    targetAddressInput = addressInput;
    const modal = new bootstrap.Modal(document.getElementById('mapModal'));
    modal.show();

    setTimeout(() => {
        if (!map) {
            initMapPicker();
        }
        const hasLatLng = latInput.value && lngInput.value;
        if (map && hasLatLng) {
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);
            if (!isNaN(lat) && !isNaN(lng)) {
                const pos = new google.maps.LatLng(lat, lng);
                map.panTo(pos);
                map.setZoom(15);
                placeMarker(pos);
                return;
            }
        }
        // nếu chưa có lat/lng, thử geocode địa chỉ
        if (map && addressInput.value) {
            geocodeAddress(addressInput.value, (loc) => {
                map.panTo(loc);
                map.setZoom(15);
                placeMarker(loc);
            });
        }
        if (map) {
            google.maps.event.trigger(map, 'resize');
        }
    }, 150);
}

function waitForGoogleMaps(callback, retries = 10) {
    if (window.google && window.google.maps && window.google.maps.Map) {
        callback();
        return;
    }
    if (retries > 0) {
        setTimeout(() => waitForGoogleMaps(callback, retries - 1), 200);
    } else {
        alert('Không thể tải Google Maps. Vui lòng kiểm tra kết nối internet và API key.');
    }
}

function viewMapOnList(address, latitude, longitude) {
    document.getElementById('viewAddress').textContent = address || 'Không có địa chỉ';
    const mapEl = document.getElementById('mapViewContainer');
    if (mapEl) {
        mapEl.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Đang tải...</span></div><p class="mt-2">Đang tải bản đồ...</p></div>';
    }
    
    const modal = new bootstrap.Modal(document.getElementById('mapViewModal'));
    modal.show();
    
    waitForGoogleMaps(() => {
        // Khởi tạo geocoder nếu chưa có
        if (!geocoder) {
            geocoder = new google.maps.Geocoder();
        }
        
        setTimeout(() => {
            const mapEl = document.getElementById('mapViewContainer');
            if (!mapEl) return;
            
            let center = { lat: 21.0278, lng: 105.8342 }; // Mặc định Hà Nội
            
            if (latitude && longitude && latitude !== 'null' && longitude !== 'null' && !isNaN(latitude) && !isNaN(longitude)) {
                center = { lat: parseFloat(latitude), lng: parseFloat(longitude) };
                initMapView(center);
            } else if (address) {
                // Geocode địa chỉ nếu không có tọa độ
                geocodeAddress(address, (loc) => {
                    center = { lat: loc.lat(), lng: loc.lng() };
                    initMapView(center);
                });
            } else {
                initMapView(center);
            }
        }, 300);
    });
}

function initMapView(center) {
    const mapEl = document.getElementById('mapViewContainer');
    if (!mapEl) return;
    
    // Xóa loading indicator
    mapEl.innerHTML = '';
    
    if (mapView) {
        mapView.setCenter(center);
        mapView.setZoom(15);
        if (markerView) {
            markerView.setPosition(center);
        } else {
            markerView = new google.maps.Marker({
                position: center,
                map: mapView,
            });
        }
        google.maps.event.trigger(mapView, 'resize');
        return;
    }
    
    try {
        mapView = new google.maps.Map(mapEl, {
            center: center,
            zoom: 15,
            mapTypeControl: true,
            streetViewControl: true,
        });
        
        markerView = new google.maps.Marker({
            position: center,
            map: mapView,
            title: document.getElementById('viewAddress').textContent,
        });
        
        // Trigger resize để đảm bảo map hiển thị đúng
        setTimeout(() => {
            google.maps.event.trigger(mapView, 'resize');
        }, 100);
    } catch (error) {
        console.error('Lỗi khởi tạo bản đồ:', error);
        mapEl.innerHTML = '<div class="alert alert-danger">Không thể hiển thị bản đồ. Vui lòng thử lại.</div>';
    }
}

// Mở vị trí kho trực tiếp trên Google Maps (tab mới)
function openOnGoogleMaps(address, latitude, longitude) {
    let url = null;

    if (latitude !== null && longitude !== null && !isNaN(latitude) && !isNaN(longitude)) {
        // Ưu tiên dùng toạ độ nếu có
        url = `https://www.google.com/maps?q=${latitude},${longitude}`;
    } else if (address && address.trim() !== '') {
        // Nếu chưa có toạ độ thì dùng địa chỉ
        url = 'https://www.google.com/maps?q=' + encodeURIComponent(address);
    }

    if (!url) {
        alert('Kho này chưa có địa chỉ hoặc toạ độ để mở trên bản đồ.');
        return;
    }

    window.open(url, '_blank');
}

document.getElementById('openMapAdd')?.addEventListener('click', () => {
    openMapPicker(
        document.getElementById('add_latitude'),
        document.getElementById('add_longitude'),
        document.getElementById('add_address')
    );
});

document.getElementById('openMapEdit')?.addEventListener('click', () => {
    openMapPicker(
        document.getElementById('edit_latitude'),
        document.getElementById('edit_longitude'),
        document.getElementById('edit_address')
    );
});

// Load Google Maps script ngay khi trang load
if (!document.querySelector('script[src*="maps.googleapis.com"]')) {
    const script = document.createElement('script');
    script.src = "https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&libraries=places&language=vi";
    script.async = true;
    script.defer = true;
    script.onload = function() {
        console.log('Google Maps API đã tải xong');
    };
    script.onerror = function() {
        console.error('Lỗi tải Google Maps API. Vui lòng kiểm tra API key.');
    };
    document.head.appendChild(script);
}
<?php endif; ?>
</script>

<?php include '../../includes/footer.php'; ?>
