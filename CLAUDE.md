# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

SmartAC — a Laravel 13 IoT dashboard for remotely controlling air conditioners in multiple rooms via ESP32 devices over MQTT. Real-time status is pushed to the browser through Laravel Reverb (WebSockets).

## Common Commands

```bash
# Full dev environment (server + queue + vite, concurrently)
composer run dev

# MQTT listener — must run separately alongside composer dev
php artisan mqtt:subscribe

# Run tests
composer run test

# Run a single test file
php artisan test --filter=TestClassName

# Full fresh setup
composer run setup

# Database migrations
php artisan migrate

# Scheduled tasks (run manually)
php artisan device:check-status   # checks device online/offline
php artisan ac:run-timer           # fires scheduled AC on/off timers
php artisan logs:clean             # deletes old user logs
```

The scheduler (`php artisan schedule:run`) fires `device:check-status` and `ac:run-timer` every minute, and `logs:clean` daily at 07:00.

## Architecture

### IoT Communication Flow

1. **ESP32 devices** publish to MQTT topics (`broker.hivemq.com:1883`, public, no auth).
2. **`php artisan mqtt:subscribe`** (`MqttSubscribe` command) listens in an infinite loop and updates `Cache` + DB on each message.
3. **Cache** is the authoritative real-time source for device status (`device_{id}_last_seen`, `device_status_{id}`). DB values are a fallback.
4. **Laravel Reverb** broadcasts `DeviceStatusUpdated` events to the `device-status` WebSocket channel so the browser refreshes without polling.
5. **Browser** also polls `/device-status`, `/temperature`, and `/notifications/recent` endpoints on a timer for resilience.

### MQTT Topic Scheme

| Direction | Topic | Purpose |
|-----------|-------|---------|
| Device → Server | `device/{id}/online` | Device boot announcement |
| Device → Server | `device/{id}/ping` / `device/{id}/heartbeat` | Keepalive (sets online, 60 s TTL) |
| Device → Server | `device/{id}/status` | LWT — payload `offline` marks device down |
| Device → Server | `room/{room}/ac/{n}/status` | AC state feedback from device |
| Server → Device | `device/{id}/config` | Sends room + AC list on reconnect |
| Server → Device | `room/{room}/ac/{n}/control` | AC control command (retained QoS 1) |

Room names in topics are always `strtolower(trim())`. A device is considered **online** when `last_seen` is within 15–30 seconds depending on context.

### Role System

Three roles enforced by `RoleMiddleware` via `role:admin,operator` or `role:admin` route groups:

- **user** — read-only: dashboard, temperature, notifications
- **operator** — room/AC CRUD and control
- **admin** — everything above + user management, activity logs export

Auth middleware stack on protected routes: `auth` → `active` (blocks deactivated accounts) → `activity` (updates `last_activity`).

### Key Models

- `Room` — has `device_id` (maps to ESP32), `device_status`, `last_seen`, `floor`
- `AcUnit` — belongs to Room; has `ac_number`, `brand`, `timer_on`, `timer_off`
- `AcStatus` — one-to-one with AcUnit; stores `power`, `mode`, `set_temperature`, `fan_speed`, `swing`
- `RoomTemperature` — append-only time-series; use `RoomTemperature::normalizeRoomName()` and `latestByNormalizedRoom()` for lookups
- `Notification` — broadcast (null `user_id`) or per-user; use static helpers `Notification::deviceOffline()` and `Notification::tempAlert()` which deduplicate via Cache

### AC Control Flow

`AcControlController` → updates `AcStatus` → calls `MqttService::publish("room/{room}/ac/{n}/control", …, QoS 1, retain=true)` → `UserLog::create()`. All control actions are logged. The MQTT subscriber also echoes control messages back to update DB state on receipt.

### Timer System

`RunAcTimer` (`ac:run-timer`) runs every minute. It fires if `now` is within a ±30 s / +60 s window of `timer_on`/`timer_off`, guarded by Cache locks (`lock:timer_{type}_{id}_v{version}_{date_time}`) and a 60 s cooldown (`ac_cooldown_{id}`) to prevent double-execution.

### Energy Analytics

`config/smartac.php` holds `energy.power_kw`, `energy.tariff_per_kwh`, and `energy.default_session_hours`, all overridable via `.env` (`SMARTAC_POWER_KW`, etc.). `EnergyController` uses these for cost estimates.

### Frontend

Blade templates in `resources/views/`. Sidebar and bottom-nav are Blade components (`components/sidebar`, `components/bottom-nav`). No separate SPA — Alpine.js / vanilla JS handles live updates by polling JSON API endpoints.
