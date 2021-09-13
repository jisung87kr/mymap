@extends('layouts.main')

@section('content')
<h1 class="mt-5">{{$data['search']}} {{$data['pageTitle']}}</h1>
<div class="btnbox" style="display: none">
    <a href="#" class="btn btn-sm btn-secondary mb-2" data-field="전체">전체</a>
</div>
<div class="map_wrap">
    <div id="map" class="mb-3" style="width:100%; height:400px;"></div>
    <button class="btn-current-position"><i class="fa fa-location-arrow" aria-hidden="true"></i></button>
</div>
<form class="row g-3 mb-3" action="" onsubmit="searchCity(event)">
    <div class="col-auto">
        <input type="text" class="form-control" placeholder="춘천시" name="city" id="city" value="">
    </div>
    <div class="col-auto">
        <input type="submit" class="btn btn-primary">
    </div>
    <small class="text-muted m-0">*도시명을 검색하세요</small>
</form>
<div class="table-responsive">
    <table class="info-table table table-bordered display responsive nowrap" style="width:100%">
        <colgroup>
            <col width="15%">
            <col width="15%">
            <col width="*">
            <col width="10%">
            <col width="15%">
            <col width="10%">
        </colgroup>
        <thead>
        <tr>
            <th>구분</th>
            <th>화장실명</th>
            <th>주소</th>
            <th>남녀공용</th>
            <th>개방시간</th>
            <th>조회</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<script type="text/javascript" src="//dapi.kakao.com/v2/maps/sdk.js?appkey=6f68d469e8f45654425303a50b45a3e7&libraries=services,clusterer,drawing"></script>
<script defer>
    var base_url = '.';
    var endpoint = base_url+'/api/v1/';
    var mapType = 'toilet/';
    var requestUrl = endpoint+mapType;
    var useFields = [
        ['남성용-장애인용대변기수', '장애인대변기(남성)'],
        ['여성용-장애인용대변기수', '장애인대변기(여성)'],
    ];
    var dataTableFields = [
        { data: '구분' },
        { data: '화장실명' },
        { data: '주소' },
        { data: '남녀공용화장실여부' },
        { data: '개방시간' },
        { defaultContent: "<button class='btn btn-outline-secondary btn-sm'>위치</button>" }
    ];

    var dt;
    var mapContainer = document.getElementById('map'), // 지도를 표시할 div
        mapOption = {
            center: new kakao.maps.LatLng(37.87446532, 127.7038534334), // 지도의 중심좌표
            level: 3 // 지도의 확대 레벨
        };

    var map = new kakao.maps.Map(mapContainer, mapOption); // 지도를 생성합니다
    var markers = [];
    var infoWindows = [];
    var geocoder = new kakao.maps.services.Geocoder();
    var clusterer = new kakao.maps.MarkerClusterer({
        map: map, // 마커들을 클러스터로 관리하고 표시할 지도 객체
        averageCenter: true, // 클러스터에 포함된 마커들의 평균 위치를 클러스터 마커 위치로 설정
        minLevel: 5 // 클러스터 할 최소 지도 레벨
    });
    // 일반 지도와 스카이뷰로 지도 타입을 전환할 수 있는 지도타입 컨트롤을 생성합니다
    var mapTypeControl = new kakao.maps.MapTypeControl();
    // 지도 확대 축소를 제어할 수 있는  줌 컨트롤을 생성합니다
    var zoomControl = new kakao.maps.ZoomControl();
    var category = {};

    map.addControl(mapTypeControl, kakao.maps.ControlPosition.TOPRIGHT);
    map.addControl(zoomControl, kakao.maps.ControlPosition.RIGHT);

    function makeFilterButtons(useFields){
        for(var i=0; i<useFields.length; i++){
            var item = useFields[i];
            var btn = '<a href="#" class="btn btn-sm btn-secondary mb-2 mx-1" data-field="'+item[0]+'">'+item[1]+'</a>';
            $(".btnbox").append(btn);
            category[item[0]] = [];
        }
    }

    function moveCurrentPosition(){
        //사용자 위치로 이동
        getCurrentXY().then(function(coords){
            var currentPosition = new kakao.maps.LatLng(coords.latitude, coords.longitude);
            map.setCenter(currentPosition);

            // 커스텀 오버레이에 표시할 내용입니다
            // HTML 문자열 또는 Dom Element 입니다
            var content = '<div class="position-absolute top-0 start-100 translate-middle p-2 bg-danger border border-light rounded-circle"></div>';

            // 커스텀 오버레이가 표시될 위치입니다
            var position = new kakao.maps.LatLng(coords.latitude, coords.longitude);

            // 커스텀 오버레이를 생성합니다
            var customOverlay = new kakao.maps.CustomOverlay({
                position: position,
                content: content
            });

            // 커스텀 오버레이를 지도에 표시합니다
            customOverlay.setMap(map);
        }).catch(function(e){
            console.log(e);
        });
    }

    function getCurrentXY(){
        return new Promise(function(resolve, reject) {
            navigator.geolocation.getCurrentPosition((position) => {
                resolve(position.coords);
            }, function(error){
                if(error.code == 1){
                    alert('사용자 위치를 찾을 수 없습니다.');
                }
            });
        });
    }

    function getAddressData(address){
        return new Promise(function(resolve, reject) {
            geocoder.addressSearch(address, function(result, status) {
                if (status === kakao.maps.services.Status.OK) {
                    resolve(result[0]);
                }
            });
        });
    }

    function createMap(data){
        // 마커초기화
        markers = [];
        setMarkers(markers, null);
        clusterer.removeMarkers( markers );
        $(".btnbox").show();
        // 마커 하나를 지도위에 표시합니다
        for (var j = 0; j < data.length; j++) {
            (function(j){
                var item = data[j];
                if(item['위도'] === null || item['경도'] === null){
                    getAddressData(item['주소']).then(function(response){
                        addMarker(new kakao.maps.LatLng(response.y, response.x), item);
                        axios.put(requestUrl+item['id'], {
                            '위도': response.y,
                            '경도': response.x
                        }).then(function(response) {
                            console.log(response);
                        });
                    });
                } else {
                    addMarker(new kakao.maps.LatLng(item['위도'], item['경도']), item);
                }
            })(j);
        }
    }

    // 마커를 생성하고 지도위에 표시하는 함수입니다
    function addMarker(position, item) {
        // 마커를 생성합니다
        var marker = new kakao.maps.Marker({
            position: position
        });

        marker.itemID = item['id'];
        marker.item = item;

        // 마커가 지도 위에 표시되도록 설정합니다
        marker.setMap(map);

        // 생성된 마커를 배열에 추가합니다
        markers.push(marker);

        // 클러스터 추가
        clusterer.addMarker(marker);

        // 마커에 표시할 인포윈도우를 생성합니다
        var el = getContent(item);
        var infowindow = new kakao.maps.InfoWindow({
            content: el, // 인포윈도우에 표시할 내용
            removable: true,
            zIndex: 100
        });

        infoWindows.push(infowindow);

        kakao.maps.event.addListener(marker, 'click', makeOverListener(map, marker, infowindow));
        kakao.maps.event.addListener(marker, 'openWindow', function(data){
            closeInfoWindows(infoWindows);
            infowindow.open(map, marker);
        });

        for(var j=0; j<useFields.length; j++){
            var fieldName = useFields[j][0];
            if(item[fieldName] != '0'){
                category[fieldName].push(marker);
            }
        }
    }

    // 인포윈도우를 표시하는 클로저를 만드는 함수입니다
    function makeOverListener(map, marker, infowindow) {
        return function() {
            closeInfoWindows(infoWindows);
            infowindow.open(map, marker);
        };
    }

    // 인포윈도우를 닫는 클로저를 만드는 함수입니다
    function makeOutListener(infowindow) {
        return function() {
            infowindow.close();
        };
    }

    // 배열에 추가된 마커들을 지도에 표시하거나 삭제하는 함수입니다
    function setMarkers(markers, map) {
        closeInfoWindows(infoWindows);
        for (var i = 0; i < markers.length; i++) {
            markers[i].setMap(map);
        }
        clusterer.clear();
        clusterer.addMarkers(markers);
    }

    function closeInfoWindows(infoWindows){
        for (var i = 0; i < infoWindows.length; i++){
            infoWindows[i].close();
        }
    }

    function searchCity(event){
        event.preventDefault();
        var city = document.getElementById('city').value;
        if(city.length < 2){
            alert('2자 이상 입력하세요');
            return false;
        }
        geocoder.addressSearch(city, function(result, status) {
            var currentPosition = new kakao.maps.LatLng(result[0].y, result[0].x);
            map.setCenter(currentPosition);
            map.setLevel(mapOption.level);
        });

        axios.get(requestUrl+city).then(function (response) {
            createMap(response.data.response);
            initDataTable(response.data.response);
        });
    }

    function initDataTable(data){
        dt = $('.info-table').DataTable();
        dt.destroy();
        dt = $('.info-table').DataTable({
            data: data,
            columns: dataTableFields,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.1/i18n/ko.json'
            }
        });
    }

    function findMarker(markers, id){
        var marker = '';
        for (var i = 0; i < markers.length; i++) {
            var item = markers[i];
            if(item['itemID'] === id){
                marker = item;
            }
        }
        return marker;
    }

    function getSelectedItems(markers){
        var arr = [];
        for (var i = 0; i < markers.length; i++) {
            var item = markers[i].item;
            arr.push(item);
        }
        return arr;
    }

    function getContent(item){
        var element = '<div class="info small">'+
                '<div class="info__name mb-1"><b>'+item['화장실명']+'['+item['구분']+']</b></div>'+
                '<div class="info__type">남여공용사용여부 : '+item['남녀공용화장실여부']+'</div>'+
                '<div class="info__use">개방시간 : '+item['개방시간']+'</div>'+
                '<div class="info__tablebox mt-2">'+
                '<table class="info__table table table-bordered">'+
                '<thead>'+
                    '<tr>'+
                    '<th>구분</th>'+
                    '<th>대</th>'+
                    '<th>소</th>'+
                    '<th><i class="fa fa-wheelchair" aria-hidden="true"></i> 대</th>'+
                    '<th><i class="fa fa-wheelchair" aria-hidden="true"></i> 소</th>'+
                    '</tr>'+
                '</thead>'+
                '<tbody>'+
                    '<tr>'+
                    '<td>남</td>'+
                    '<td>'+item['남성용-대변기수']+'개</td>'+
                    '<td>'+item['남성용-소변기수']+'개</td>'+
                    '<td>'+item['남성용-장애인용대변기수']+'개</td>'+
                    '<td>'+item['남성용-장애인용소변기수']+'개</td>'+
                    '</tr>'+
                    '<tr>'+
                    '<td>여</td>'+
                    '<td>'+item['여성용-장애인용대변기수']+'개</td>'+
                    '<td>-</td>'+
                    '<td>'+item['여성용-어린이용대변기수']+'개</td>'+
                    '<td>-</td>'+
                    '</tr>'+
                '</tbody>'+
                '</table>'+
                '</div>'+
                '<div class="info__time">데이터기준일자 : '+item['데이터기준일자']+'</div>'+
                '<div class="info__findroad">'+
                '<a href="https://map.kakao.com/link/to/'+item['화장실명']+','+item['위도']+','+item['경도']+'" target="_blank">길찾기 <i class="fa fa-share-square-o" aria-hidden="true"></i></a>'+
                '</div>'+
            '</div>';
        return element;
    }

    $('.table tbody').on( 'click', 'button', function () {
        var data = dt.row( $(this).parents('tr') ).data();
        var currentPosition = new kakao.maps.LatLng(data['위도'], data['경도']);
        map.setCenter(currentPosition);
        var marker = findMarker(markers, data['id']);
        kakao.maps.event.trigger(marker, 'openWindow');
        map.setLevel(mapOption.level);
        $(window).scrollTop($(".map_wrap").offset().top - 200);
    } );

    $(".btnbox").on("click", 'a', function(event){
       event.preventDefault();
       var field = $(this).data('field');
       var selected_markers = [];
       if(field === '전체'){
           selected_markers = markers;
       } else {
           selected_markers = category[field];
       }
       setMarkers(selected_markers, null);
       initDataTable(getSelectedItems(selected_markers));

       $(this).addClass("btn-primary").removeClass("btn-secondary").siblings().addClass("btn-secondary").removeClass("btn-primary");
    });

    $(".btn-current-position").click(moveCurrentPosition);
    makeFilterButtons(useFields);
</script>
@endsection