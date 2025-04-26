define([
    'jquery'
], function($) {
    'use strict';
    
    return {
        maps: {},
        mapInitQueue: [],
        
        /**
         * Initialize when DOM is ready
         */
        initialize: function() {
            var self = this;
            // Fix for the bind error - don't use document.addEventListener with bind
            document.addEventListener('google-maps-loaded', function() {
                self.processMapQueue();
            });
        },
        
        /**
         * Process all queued map initializations
         */
        processMapQueue: function() {
            if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                console.log('Google Maps API not loaded yet');
                return; // Maps API not loaded yet
            }
            
            console.log('Processing map queue with ' + this.mapInitQueue.length + ' maps');
            for (var i = 0; i < this.mapInitQueue.length; i++) {
                var mapConfig = this.mapInitQueue[i];
                this.createMap(
                    mapConfig.mapId,
                    mapConfig.latitude,
                    mapConfig.longitude,
                    mapConfig.zoom,
                    mapConfig.markerTitle
                );
            }
            
            // Clear the queue
            this.mapInitQueue = [];
        },
        
        /**
         * Queue up map initialization requests until Google Maps is loaded
         * 
         * @param {String} mapId
         * @param {Float} latitude
         * @param {Float} longitude
         * @param {Integer} zoom
         * @param {String} markerTitle
         */
        initMap: function(mapId, latitude, longitude, zoom, markerTitle) {
            console.log('Adding map to queue: ' + mapId);
            
            // Add to queue
            this.mapInitQueue.push({
                mapId: mapId,
                latitude: latitude,
                longitude: longitude,
                zoom: zoom,
                markerTitle: markerTitle
            });
            
            // If Google Maps is already loaded, process immediately
            if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
                console.log('Google Maps already loaded, processing queue immediately');
                this.processMapQueue();
            } else {
                console.log('Google Maps not loaded yet, waiting for callback');
            }
        },
        
        /**
         * Create a Google Map instance
         * 
         * @param {String} mapId
         * @param {Float} latitude
         * @param {Float} longitude
         * @param {Integer} zoom
         * @param {String} markerTitle
         */
        createMap: function(mapId, latitude, longitude, zoom, markerTitle) {
            console.log('Creating map: ' + mapId);
            var mapElement = document.getElementById(mapId);
            if (!mapElement) {
                console.error('Map container not found: ' + mapId);
                return;
            }
            
            var position = {
                lat: parseFloat(latitude),
                lng: parseFloat(longitude)
            };
            
            var mapOptions = {
                center: position,
                zoom: parseInt(zoom) || 15,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                scrollwheel: false
            };
            
            try {
                this.maps[mapId] = new google.maps.Map(mapElement, mapOptions);
                
                // Create a marker
                var marker = new google.maps.Marker({
                    position: position,
                    map: this.maps[mapId],
                    title: markerTitle,
                    animation: google.maps.Animation.DROP
                });
                
                // Add info window if title is provided
                if (markerTitle) {
                    var self = this;
                    var infoWindow = new google.maps.InfoWindow({
                        content: '<div class="map-info-window"><strong>' + markerTitle + '</strong></div>'
                    });
                    
                    marker.addListener('click', function() {
                        infoWindow.open(self.maps[mapId], marker);
                    });
                }
                
                console.log('Map created successfully: ' + mapId);
            } catch (e) {
                console.error('Error creating map:', e);
            }
        }
    };
});