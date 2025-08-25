<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get Access Token - Nuansa Accurate API</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .modal-backdrop {
            backdrop-filter: blur(4px);
        }
        
        .loading-spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-key text-red-600 mr-3 text-2xl"></i>
                    <h1 class="text-3xl font-bold text-gray-900">Get Access Token</h1>
                </div>
                <div class="flex gap-4">
                    <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-home mr-2"></i>Dashboard
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">OAuth Access Token</h2>
            
            <!-- Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                    <span class="font-medium text-blue-800">Information</span>
                </div>
                <p class="text-blue-700 mt-2">
                    This page will automatically attempt to get an access token from the Accurate API.
                    If successful, the token will be saved and you can use it to access the API.
                </p>
            </div>

            <!-- Warning if no code -->
            <div id="noCodeWarning" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6 hidden">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                    <span class="font-medium text-yellow-800">No Authorization Code</span>
                </div>
                <p class="text-yellow-700 mt-2">
                    No authorization code found in the URL. You need to get a new authorization code first before you can obtain an access token.
                </p>
            </div>

            <!-- Get Token Button -->
            <div class="text-center">
                <button id="getTokenBtn" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                    <i class="fas fa-key mr-2"></i>Get Access Token
                </button>
                
                <!-- Get New Authorization Button -->
                <div class="mt-4">
                    <a href="oauth/authorize.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors inline-block">
                        <i class="fas fa-refresh mr-2"></i>Get New Authorization Code
                    </a>
                </div>
            </div>

            <!-- Result Section -->
            <div id="resultSection" class="mt-6 hidden">
                <div id="successResult" class="hidden bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-600 mr-2"></i>
                        <span class="font-medium text-green-800">Success!</span>
                    </div>
                    <p class="text-green-700 mt-2">Access token has been successfully obtained and saved.</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Error Modal -->
    <div id="errorModal" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-red-600 mr-3 text-xl"></i>
                        <h3 class="text-lg font-semibold text-gray-900">Token Error</h3>
                    </div>
                </div>
                
                <!-- Modal Content -->
                <div class="px-6 py-4">
                    <p id="errorMessage" class="text-gray-700 mb-4"></p>
                    
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <div class="flex items-center">
                            <i class="fas fa-lightbulb text-yellow-600 mr-2"></i>
                            <span class="font-medium text-yellow-800">Suggestion</span>
                        </div>
                        <p class="text-yellow-700 text-sm mt-1">
                            Try getting a new authorization code or check your OAuth configuration.
                        </p>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-gray-200 flex justify-between">
                    <a href="oauth/authorize.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-key mr-2"></i>Get New Authorization
                    </a>
                    <button id="closeErrorModal" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div id="loadingModal" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl p-6 text-center">
                <div class="loading-spinner mx-auto mb-4"></div>
                <p class="text-gray-700">Getting access token...</p>
            </div>
        </div>
    </div>

    <script>
        // Elements
        const getTokenBtn = document.getElementById('getTokenBtn');
        const errorModal = document.getElementById('errorModal');
        const loadingModal = document.getElementById('loadingModal');
        const closeErrorModal = document.getElementById('closeErrorModal');
        const errorMessage = document.getElementById('errorMessage');
        const resultSection = document.getElementById('resultSection');
        const successResult = document.getElementById('successResult');

        // Event listeners
        getTokenBtn.addEventListener('click', getAccessToken);
        closeErrorModal.addEventListener('click', closeModal);
        
        // Close modal when clicking backdrop
        errorModal.addEventListener('click', (e) => {
            if (e.target === errorModal) {
                closeModal();
            }
        });

        // Close modal with ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !errorModal.classList.contains('hidden')) {
                closeModal();
            }
        });

        function showLoading() {
            loadingModal.classList.remove('hidden');
        }

        function hideLoading() {
            loadingModal.classList.add('hidden');
        }

        function showError(message) {
            errorMessage.textContent = message;
            errorModal.classList.remove('hidden');
        }

        function closeModal() {
            errorModal.classList.add('hidden');
        }

        function showSuccess() {
            resultSection.classList.remove('hidden');
            successResult.classList.remove('hidden');
        }
        
        function showScopeInfo(scopes) {
            let scopeText = '';
            let availableCount = 0;
            
            for (const [scope, available] of Object.entries(scopes)) {
                if (available) {
                    scopeText += `✅ ${scope}\n`;
                    availableCount++;
                } else {
                    scopeText += `❌ ${scope}\n`;
                }
            }
            
            const scopeDiv = document.createElement('div');
            scopeDiv.className = 'mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg';
            scopeDiv.innerHTML = `
                <h4 class="font-medium text-blue-800 mb-2">Available Scopes (${availableCount}/${Object.keys(scopes).length}):</h4>
                <pre class="text-sm text-blue-700">${scopeText}</pre>
                <p class="text-sm text-blue-600 mt-2">You will be redirected to token status page in 3 seconds...</p>
            `;
            
            successResult.appendChild(scopeDiv);
        }

        function getAccessToken() {
            showLoading();
            
            // Get authorization code from URL if available
            const urlParams = new URLSearchParams(window.location.search);
            const code = urlParams.get('code');
            
            if (!code) {
                hideLoading();
                showError('No authorization code found in URL');
                return;
            }
            
            fetch(`get_access_token.php?code=${encodeURIComponent(code)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);
                    return response.text().then(text => {
                        console.log('Raw response:', text);
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            throw new Error('Invalid JSON response: ' + text);
                        }
                    });
                })
                .then(data => {
                    hideLoading();
                    
                    if (data.success) {
                        showSuccess();
                        getTokenBtn.textContent = 'Token Obtained Successfully';
                        getTokenBtn.disabled = true;
                        getTokenBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
                        getTokenBtn.classList.add('bg-green-600', 'cursor-not-allowed');
                        
                        // Show additional info if available
                        if (data.data && data.data.available_scopes) {
                            showScopeInfo(data.data.available_scopes);
                        }
                        
                        // Auto redirect to token status after 3 seconds
                        setTimeout(() => {
                            window.location.href = 'oauth/token-status.php';
                        }, 3000);
                        
                    } else {
                        // Show detailed error information
                        let errorMsg = data.message || 'Failed to get access token';
                        if (data.data && data.data.error) {
                            errorMsg += '\n\nDetails: ' + data.data.error;
                            
                            // Check for specific error types
                            if (data.data.error === 'invalid_grant') {
                                errorMsg += '\n\nThis error means the authorization code is expired, already used, or invalid.';
                                errorMsg += '\nPlease get a new authorization code by clicking "Get New Authorization" below.';
                            }
                        }
                        if (data.data && data.data.http_code) {
                            errorMsg += '\nHTTP Code: ' + data.data.http_code;
                        }
                        showError(errorMsg);
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error:', error);
                    showError('Network error or invalid response: ' + error.message);
                });
        }

        // Auto-trigger if there's a code in URL
        window.addEventListener('load', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const noCodeWarning = document.getElementById('noCodeWarning');
            
            if (urlParams.get('code')) {
                getAccessToken();
            } else {
                // Show warning if no code
                noCodeWarning.classList.remove('hidden');
                getTokenBtn.disabled = true;
                getTokenBtn.classList.add('cursor-not-allowed', 'opacity-50');
                getTokenBtn.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i>No Authorization Code';
            }
        });
    </script>
</body>
</html>
