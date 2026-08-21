#!/bin/sh
# Normalisasi env database untuk deployment Render + Supabase.
#
# Di Render (private network): tetap pakai hostname INTERNAL (dpg-xxx-a).
#   SSL tidak wajib — DB_SSLMODE=prefer agar tidak gagal handshake.
# Di luar Render: hostname EXTERNAL (*.postgres.render.com) + sslmode=require.
# URL Supabase / non-Render tidak diubah selain memastikan sslmode.

ensure_sslmode() {
    url="$1"
    mode="$2"

    case "$url" in
        *sslmode=*)
            printf '%s' "$url" | sed -E "s/([?&])sslmode=[^&]*/\1sslmode=${mode}/"
            ;;
        *\?*)
            printf '%s&sslmode=%s' "$url" "$mode"
            ;;
        *)
            printf '%s?sslmode=%s' "$url" "$mode"
            ;;
    esac
}

# External → internal: dpg-xxx-a.region-postgres.render.com → dpg-xxx-a
to_internal_render_host() {
    printf '%s' "$1" | sed -E 's@(dpg-[a-z0-9]+-a)\.[a-z0-9-]+-postgres\.render\.com@\1@'
}

# Internal pendek → external (hanya bila perlu di luar Render)
to_external_render_host() {
    url="$1"
    region="${RENDER_REGION:-singapore}"

    case "$url" in
        *postgres.render.com*|*supabase.com*|*pooler.supabase.com*)
            printf '%s' "$url"
            return 0
            ;;
    esac

    printf '%s' "$url" | sed -E "s/(dpg-[a-z0-9]+-a)([:/])/\1.${region}-postgres.render.com\2/"
}

normalize_db_url() {
    url="$1"
    [ -z "$url" ] && return 0

    # Deteksi runtime Render (Render menyetel RENDER=true / RENDER_SERVICE_ID)
    on_render=0
    if [ "${RENDER:-}" = "true" ] || [ -n "${RENDER_SERVICE_ID:-}" ] || [ -n "${RENDER_INSTANCE_ID:-}" ]; then
        on_render=1
    fi

    case "$url" in
        *supabase.com*|*pooler.supabase.com*)
            # Supabase selalu butuh SSL (baik lokal maupun di Render)
            export DB_SSLMODE=require
            ensure_sslmode "$url" "require"
            return 0
            ;;
    esac

    if [ "$on_render" = "1" ]; then
        # App di Render → pakai internal hostname, jangan paksa SSL
        url="$(to_internal_render_host "$url")"
        # Hapus sslmode=require yang sering bikin "SSL connection has been closed"
        url="$(printf '%s' "$url" | sed -E 's/[?&]sslmode=[^&]*//g' | sed -E 's/\?$//' | sed -E 's/\?&/?/' | sed -E 's/&&/\&/g')"
        # Paksa prefer — override DB_SSLMODE=require dari render.yaml
        export DB_SSLMODE=prefer
        printf '%s' "$url"
        return 0
    fi

    # Di luar Render (lokal / CI) → external + SSL
    url="$(to_external_render_host "$url")"
    export DB_SSLMODE="${DB_SSLMODE:-require}"
    ensure_sslmode "$url" "require"
}

if [ -n "$DATABASE_URL" ]; then
    DATABASE_URL="$(normalize_db_url "$DATABASE_URL")"
    export DATABASE_URL
fi

if [ -n "$DB_URL" ]; then
    DB_URL="$(normalize_db_url "$DB_URL")"
    export DB_URL
fi

if [ -z "$DB_URL" ] && [ -n "$DATABASE_URL" ]; then
    DB_URL="$DATABASE_URL"
    export DB_URL
fi

if [ -n "$DATABASE_URL" ] || [ -n "$DB_URL" ]; then
    unset DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD
fi

# Default SSL mode bila belum diset
if [ -z "${DB_SSLMODE:-}" ]; then
    if [ "${RENDER:-}" = "true" ] || [ -n "${RENDER_SERVICE_ID:-}" ]; then
        export DB_SSLMODE=prefer
    else
        export DB_SSLMODE=require
    fi
fi

rm -f public/hot
