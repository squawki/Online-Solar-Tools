# Online-Solar-Tools

## Online Solar Tools API Documentation

[Online Solar Tools](https://onlinesolartools.com/) offers a free API for a range of solar calculations and open sources it's use to be used on your own infrastructure under the MIT licence.

---

### 1. `POST /sun_visible`
**Description**: Determines if the sun is currently visible at a specified coordinate. You may also specify a time to check past or future visibility.

- **Endpoint**: `/sun_visible`
- **Parameters**:
  - `latitude` (required): Latitude in decimal degrees
  - `longitude` (required): Longitude in decimal degrees
  - `datetime` (optional): UTC timestamp to check a different time

#### Example Response
- **Success (200)**:
    ```json
    {
        "visible": 0
    }
    ```
- **Error (400)**: 
    ```json
    {
        "error": "Invalid latitude or longitude"
    }
    ```

---

### 2. `POST /sunrise-sunset`
**Description**: Provides the sunrise and sunset times for a specified location, with optional timezone adjustment.

- **Endpoint**: `/sunrise-sunset`
- **Parameters**:
  - `latitude` (required): Latitude in decimal degrees
  - `longitude` (required): Longitude in decimal degrees
  - `datetime` (optional): UTC timestamp to specify a particular day
  - `timezoneOffset` (optional): Offset in hours to adjust the returned times

#### Example Response
- **Success (200)**:
    ```json
    {
        "sunrise": "2024-11-09T04:24:34Z",
        "sunset": "2024-11-09T15:43:08Z"
    }
    ```
- **Error (400)**:
    ```json
    {
        "error": "Invalid latitude or longitude. Ensure both are provided and within valid ranges."
    }
    ```

---

### 3. `POST /sun_position`
**Description**: Provides the altitude and azimuth of the sun at a specified coordinate.

- **Endpoint**: `POST /sun_position`
- **Parameters**:
  - `latitude` (required): Latitude in decimal degrees
  - `longitude` (required): Longitude in decimal degrees
  - `datetime` (optional): UTC timestamp to check a different time

#### Example Response
- **Success (200)**:
    ```json
    {
        "altitude": 13.88,
        "azimuth": 110.68
    }
    ```
- **Error (400)**:
    ```json
    {
        "error": "Invalid latitude or longitude. Ensure both are provided and within valid ranges."
    }
    ```

--- 
