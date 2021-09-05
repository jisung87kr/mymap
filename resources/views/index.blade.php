@extends('layouts.main')

@section('content')
<h1>{{$city}} 공중화장실 정보</h1>
<div id="map" class="mb-3" style="width:100%; height:400px;"></div>
<form class="row g-3 mb-3" action="" onsubmit="searchCity(event)">
    <div class="col-auto">
        <input type="text" class="form-control" placeholder="춘천" name="city" id="city">
    </div>
    <div class="col-auto">
        <input type="submit" class="btn btn-primary">
    </div>
</form>
<table class="table table-bordered">
    <colgroup>
        <col width="15%">
        <col width="15%">
        <col width="*">
        <col width="10%">
        <col width="15%">
    </colgroup>
    <thead>
    <tr>
        <th>구분</th>
        <th>화장실명</th>
        <th>주소</th>
        <th>남녀공용</th>
        <th>개방시간</th>
    </tr>
    </thead>
    <tbody>
        @foreach ($toilets as $toilet)
        <tr>
            <td>{{$toilet->구분}}</td>
            <td>{{$toilet->화장실명}}</td>
            <td>{{$toilet->주소}}</td>
            <td>{{$toilet->남녀공용화장실여부}}</td>
            <td>{{$toilet->개방시간}}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<script type="text/javascript" src="//dapi.kakao.com/v2/maps/sdk.js?appkey=6f68d469e8f45654425303a50b45a3e7&libraries=services,clusterer,drawing"></script>
<script defer>
    var mapContainer = document.getElementById('map'), // 지도를 표시할 div
        mapOption = {
            center: new kakao.maps.LatLng(37.87446532, 127.7038534334), // 지도의 중심좌표
            level: 3 // 지도의 확대 레벨
        };

    var map = new kakao.maps.Map(mapContainer, mapOption); // 지도를 생성합니다
    var markers = [];
    var geocoder = new kakao.maps.services.Geocoder();
    var clusterer = new kakao.maps.MarkerClusterer({
        map: map, // 마커들을 클러스터로 관리하고 표시할 지도 객체
        averageCenter: true, // 클러스터에 포함된 마커들의 평균 위치를 클러스터 마커 위치로 설정
        minLevel: 5 // 클러스터 할 최소 지도 레벨
    });

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
    });

    function getCurrentXY(){
        return new Promise(function(resolve, reject) {
            navigator.geolocation.getCurrentPosition((position) => {
                // resolve(position.coords.latitude, position.coords.longitude);
                resolve(position.coords);
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

    // 인포윈도우를 표시하는 클로저를 만드는 함수입니다
    function makeOverListener(map, marker, infowindow) {
        return function() {
            infowindow.open(map, marker);
        };
    }

    // 인포윈도우를 닫는 클로저를 만드는 함수입니다
    function makeOutListener(infowindow) {
        return function() {
            infowindow.close();
        };
    }

    function createMap(data){
        // 마커초기화
        setMarkers(null);
        clusterer.removeMarkers( markers );

        // 마커 하나를 지도위에 표시합니다
        for (var j = 0; j < data.length; j++) {
            (function(j){
                var item = data[j];
                if(item['위도'] === null || item['경도'] === null){
                    getAddressData(item['주소']).then(function(response){
                        addMarker(new kakao.maps.LatLng(response.y, response.x), item);
                        axios.put('//mymap.test/api/v1/'+item['id'], {
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

        // 마커를 생성하고 지도위에 표시하는 함수입니다
        function addMarker(position, item) {
            // 마커를 생성합니다
            var marker = new kakao.maps.Marker({
                position: position
            });

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
                removable: true
            });

            kakao.maps.event.addListener(marker, 'click', makeOverListener(map, marker, infowindow));
        }

        // 배열에 추가된 마커들을 지도에 표시하거나 삭제하는 함수입니다
        function setMarkers(map) {
            for (var i = 0; i < markers.length; i++) {
                markers[i].setMap(map);
            }
        }
    }

    function searchCity(event){
        event.preventDefault();
        var city = document.getElementById('city').value;
        axios.get('//mymap.test/api/v1/'+city).then(function (response) {
            createMap(response.data.response);
            var dt = $('table').DataTable();
            dt.destroy();
            $('table').DataTable({
                data: response.data.response,
                columns: [
                    { data: '구분' },
                    { data: '화장실명' },
                    { data: '주소' },
                    { data: '남녀공용화장실여부' },
                    { data: '개방시간' },
                ]
            });
        });
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


</script>
@endsection