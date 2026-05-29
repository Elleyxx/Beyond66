<?php
function createJwt(array $payload): string {
    return base64_encode(json_encode($payload));
}
