FROM node:22.21.1-bookworm-slim AS ui

WORKDIR /app

COPY frontend/package.json frontend/package-lock.json ./

RUN npm ci

COPY frontend/ ./

RUN npm run build

FROM nginx:1.28.0-alpine

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=ui /app/dist /usr/share/nginx/html
