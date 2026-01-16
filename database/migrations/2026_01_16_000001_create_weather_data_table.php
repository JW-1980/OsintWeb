<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('weather_data', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Location (spatial point)
            $table->geometry('location', 'point', 4326)->comment('Geographic point location');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('location_name')->nullable()->comment('Human-readable location name');

            // Time information
            $table->dateTime('recorded_at')->comment('When this weather data was recorded/observed');

            // Temperature
            $table->decimal('temperature', 5, 2)->nullable()->comment('Temperature in Celsius');
            $table->decimal('feels_like', 5, 2)->nullable()->comment('Feels like temperature in Celsius');
            $table->decimal('temperature_min', 5, 2)->nullable()->comment('Minimum temperature');
            $table->decimal('temperature_max', 5, 2)->nullable()->comment('Maximum temperature');

            // Humidity
            $table->unsignedTinyInteger('humidity')->nullable()->comment('Humidity percentage 0-100');

            // Wind
            $table->decimal('wind_speed', 6, 2)->nullable()->comment('Wind speed in m/s');
            $table->decimal('wind_direction', 5, 2)->nullable()->comment('Wind direction in degrees 0-360');
            $table->decimal('wind_gust', 6, 2)->nullable()->comment('Wind gust speed in m/s');

            // Visibility & clouds
            $table->unsignedInteger('visibility')->nullable()->comment('Visibility in meters');
            $table->unsignedTinyInteger('cloud_cover')->nullable()->comment('Cloud cover percentage 0-100');

            // Precipitation
            $table->enum('precipitation_type', ['none', 'rain', 'drizzle', 'snow', 'sleet', 'hail', 'freezing_rain'])->default('none');
            $table->decimal('precipitation_amount', 6, 2)->nullable()->comment('Precipitation amount in mm');
            $table->decimal('snow_depth', 6, 2)->nullable()->comment('Snow depth in cm');

            // Atmospheric pressure
            $table->decimal('pressure', 6, 1)->nullable()->comment('Atmospheric pressure in hPa');
            $table->decimal('pressure_sea_level', 6, 1)->nullable()->comment('Sea level pressure in hPa');
            $table->decimal('pressure_ground_level', 6, 1)->nullable()->comment('Ground level pressure in hPa');

            // UV & radiation
            $table->decimal('uv_index', 4, 2)->nullable()->comment('UV index 0-11+');

            // Sun position and times
            $table->time('sunrise')->nullable()->comment('Sunrise time (local)');
            $table->time('sunset')->nullable()->comment('Sunset time (local)');
            $table->decimal('sun_azimuth', 6, 2)->nullable()->comment('Sun azimuth angle in degrees');
            $table->decimal('sun_elevation', 5, 2)->nullable()->comment('Sun elevation angle in degrees');
            $table->decimal('solar_noon', 8, 4)->nullable()->comment('Solar noon time as decimal hours');

            // Weather condition
            $table->enum('weather_condition', [
                'clear',
                'partly_cloudy',
                'cloudy',
                'overcast',
                'fog',
                'mist',
                'haze',
                'rain',
                'drizzle',
                'snow',
                'thunderstorm',
                'sleet',
                'dust',
                'sand',
                'smoke',
                'unknown'
            ])->default('unknown');
            $table->string('weather_description')->nullable()->comment('Detailed weather description');
            $table->string('weather_icon')->nullable()->comment('Weather icon code from provider');

            // Data source information
            $table->string('data_source')->comment('API provider name (openweathermap, visualcrossing, etc.)');
            $table->string('data_source_id')->nullable()->comment('ID/reference from the data source');
            $table->enum('data_type', ['current', 'historical', 'forecast'])->default('current');

            // Raw response for debugging/additional data
            $table->json('raw_data')->nullable()->comment('Raw API response JSON');

            // Metadata
            $table->foreignId('fetched_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fetched_at')->nullable()->comment('When data was fetched from API');

            $table->timestamps();

            // Indexes
            $table->index('recorded_at');
            $table->index(['latitude', 'longitude']);
            $table->index('data_source');
            $table->index('weather_condition');
            $table->index('created_at');

            // Spatial index
            $table->spatialIndex('location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_data');
    }
};
