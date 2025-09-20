<?php

use App\Mcp\Servers\EcoTrackerServer;
use Laravel\Mcp\Facades\Mcp;

// Mcp::web('/mcp/demo', \App\Mcp\Servers\PublicServer::class);
Mcp::web('eco-tracker', EcoTrackerServer::class)->middleware('auth:sanctum');
