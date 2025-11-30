@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
    <!-- Full Screen Background Image -->
    <div id="background-image" class="absolute inset-0 z-0 pointer-events-none min-h-screen">
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat transition-opacity duration-500 ease-in-out" style="background-image: url('https://wassit.diaszone.com/storage/home_page/degaultbanner.webp'); opacity: 1;"></div>
        <div class="absolute inset-0" style="background: linear-gradient(to bottom, rgba(15, 17, 27, 0.85) 0%, rgba(15, 17, 27, 0.92) 50%, rgba(15, 17, 27, 0.98) 100%);"></div>
    </div>
    
    <!-- Content Overlay -->
    <div class="relative z-10 min-h-screen pt-20 sm:pt-24 pb-12">
        <div class="px-4 mx-auto max-w-4xl sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl mb-4">
                    Become a <span class="text-transparent bg-clip-text bg-gradient-to-br from-[#A3DFFF] via-[#6CB9FF] to-[#0185FF]">Partner</span>
                </h1>
                <p class="text-lg text-gray-400 max-w-2xl mx-auto">
                    Join our marketplace and start selling gaming accounts. Get access to thousands of buyers and grow your business.
                </p>
            </div>
            
            <!-- Application Form -->
            <div class="rounded-xl overflow-hidden" style="background-color: rgba(14, 16, 21, 0.75); border: 1px solid #2d2c31; backdrop-blur-md;">
                <div class="p-6 sm:p-8 lg:p-10">
                    <form x-data="{ 
                        submitted: false,
                        submitForm() {
                            this.submitted = true;
                            // Form submission logic here
                        }
                    }" @submit.prevent="submitForm()">
                        <!-- Personal Information Section -->
                        <div class="mb-8">
                            <h2 class="text-xl font-semibold text-white mb-6 flex items-center">
                                <i class="fa-solid fa-user mr-3 text-red-600"></i>
                                Personal Information
                            </h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Full Name -->
                                <div>
                                    <label for="full_name" class="block text-sm font-medium text-gray-300 mb-2">
                                        Full Name <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        id="full_name" 
                                        name="full_name" 
                                        required
                                        class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                        style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                        placeholder="John Doe"
                                    >
                                </div>
                                
                                <!-- Email -->
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
                                        Email Address <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="email" 
                                        id="email" 
                                        name="email" 
                                        required
                                        class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                        style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                        placeholder="john@example.com"
                                    >
                                </div>
                                
                                <!-- Phone -->
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-300 mb-2">
                                        Phone Number <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="tel" 
                                        id="phone" 
                                        name="phone" 
                                        required
                                        class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                        style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                        placeholder="0673771763"
                                    >
                                </div>
                                
                                <!-- Country -->
                                <div>
                                    <label for="country" class="block text-sm font-medium text-gray-300 mb-2">
                                        Country <span class="text-red-500">*</span>
                                    </label>
                                    <select 
                                        id="country" 
                                        name="country" 
                                        required
                                        class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                        style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                    >
                                        <option value="">Select Country</option>
                                        <option value="AF">Afghanistan</option>
                                        <option value="AL">Albania</option>
                                        <option value="DZ">Algeria</option>
                                        <option value="AR">Argentina</option>
                                        <option value="AU">Australia</option>
                                        <option value="AT">Austria</option>
                                        <option value="BH">Bahrain</option>
                                        <option value="BD">Bangladesh</option>
                                        <option value="BB">Barbados</option>
                                        <option value="BE">Belgium</option>
                                        <option value="BZ">Belize</option>
                                        <option value="BO">Bolivia</option>
                                        <option value="BR">Brazil</option>
                                        <option value="BN">Brunei</option>
                                        <option value="BG">Bulgaria</option>
                                        <option value="KH">Cambodia</option>
                                        <option value="CA">Canada</option>
                                        <option value="CL">Chile</option>
                                        <option value="CN">China</option>
                                        <option value="CO">Colombia</option>
                                        <option value="CR">Costa Rica</option>
                                        <option value="CU">Cuba</option>
                                        <option value="CY">Cyprus</option>
                                        <option value="CZ">Czech Republic</option>
                                        <option value="DK">Denmark</option>
                                        <option value="DO">Dominican Republic</option>
                                        <option value="EC">Ecuador</option>
                                        <option value="EG">Egypt</option>
                                        <option value="SV">El Salvador</option>
                                        <option value="EE">Estonia</option>
                                        <option value="FJ">Fiji</option>
                                        <option value="FI">Finland</option>
                                        <option value="FR">France</option>
                                        <option value="GF">French Guiana</option>
                                        <option value="PF">French Polynesia</option>
                                        <option value="DE">Germany</option>
                                        <option value="GH">Ghana</option>
                                        <option value="GR">Greece</option>
                                        <option value="GT">Guatemala</option>
                                        <option value="GY">Guyana</option>
                                        <option value="HT">Haiti</option>
                                        <option value="HN">Honduras</option>
                                        <option value="HU">Hungary</option>
                                        <option value="IS">Iceland</option>
                                        <option value="IN">India</option>
                                        <option value="ID">Indonesia</option>
                                        <option value="IR">Iran</option>
                                        <option value="IQ">Iraq</option>
                                        <option value="IE">Ireland</option>
                                        <option value="IL">Israel</option>
                                        <option value="IT">Italy</option>
                                        <option value="JM">Jamaica</option>
                                        <option value="JP">Japan</option>
                                        <option value="JO">Jordan</option>
                                        <option value="KZ">Kazakhstan</option>
                                        <option value="KE">Kenya</option>
                                        <option value="KW">Kuwait</option>
                                        <option value="KG">Kyrgyzstan</option>
                                        <option value="LA">Laos</option>
                                        <option value="LV">Latvia</option>
                                        <option value="LB">Lebanon</option>
                                        <option value="LT">Lithuania</option>
                                        <option value="LU">Luxembourg</option>
                                        <option value="MY">Malaysia</option>
                                        <option value="MT">Malta</option>
                                        <option value="MX">Mexico</option>
                                        <option value="MN">Mongolia</option>
                                        <option value="MA">Morocco</option>
                                        <option value="MM">Myanmar</option>
                                        <option value="NL">Netherlands</option>
                                        <option value="NZ">New Zealand</option>
                                        <option value="NI">Nicaragua</option>
                                        <option value="NG">Nigeria</option>
                                        <option value="NO">Norway</option>
                                        <option value="OM">Oman</option>
                                        <option value="PK">Pakistan</option>
                                        <option value="PA">Panama</option>
                                        <option value="PY">Paraguay</option>
                                        <option value="PE">Peru</option>
                                        <option value="PH">Philippines</option>
                                        <option value="PL">Poland</option>
                                        <option value="PT">Portugal</option>
                                        <option value="PR">Puerto Rico</option>
                                        <option value="QA">Qatar</option>
                                        <option value="RO">Romania</option>
                                        <option value="RU">Russia</option>
                                        <option value="SA">Saudi Arabia</option>
                                        <option value="SG">Singapore</option>
                                        <option value="SK">Slovakia</option>
                                        <option value="SI">Slovenia</option>
                                        <option value="ZA">South Africa</option>
                                        <option value="KR">South Korea</option>
                                        <option value="ES">Spain</option>
                                        <option value="SR">Suriname</option>
                                        <option value="SE">Sweden</option>
                                        <option value="CH">Switzerland</option>
                                        <option value="TJ">Tajikistan</option>
                                        <option value="TH">Thailand</option>
                                        <option value="TL">East Timor</option>
                                        <option value="TN">Tunisia</option>
                                        <option value="TR">Turkey</option>
                                        <option value="TM">Turkmenistan</option>
                                        <option value="TT">Trinidad and Tobago</option>
                                        <option value="AE">United Arab Emirates</option>
                                        <option value="GB">United Kingdom</option>
                                        <option value="US">United States</option>
                                        <option value="UY">Uruguay</option>
                                        <option value="UZ">Uzbekistan</option>
                                        <option value="VE">Venezuela</option>
                                        <option value="VN">Vietnam</option>
                                        <option value="YE">Yemen</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Business Information Section -->
                        <div class="mb-8">
                            <h2 class="text-xl font-semibold text-white mb-6 flex items-center">
                                <i class="fa-solid fa-briefcase mr-3 text-red-600"></i>
                                Business Information
                            </h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Business Name -->
                                <div>
                                    <label for="business_name" class="block text-sm font-medium text-gray-300 mb-2">
                                        Business/Store Name <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        id="business_name" 
                                        name="business_name" 
                                        required
                                        class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                        style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                        placeholder="My Gaming Store"
                                    >
                                </div>
                                
                                <!-- Website/Social Media -->
                                <div>
                                    <label for="website" class="block text-sm font-medium text-gray-300 mb-2">
                                        Website / Social Media (Optional)
                                    </label>
                                    <input 
                                        type="url" 
                                        id="website" 
                                        name="website" 
                                        class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                        style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                        placeholder="https://example.com"
                                    >
                                </div>
                                
                                <!-- Years of Experience -->
                                <div>
                                    <label for="experience" class="block text-sm font-medium text-gray-300 mb-2">
                                        Years of Experience <span class="text-red-500">*</span>
                                    </label>
                                    <select 
                                        id="experience" 
                                        name="experience" 
                                        required
                                        class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                        style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                    >
                                        <option value="">Select experience</option>
                                        <option value="0-1">Less than 1 year</option>
                                        <option value="1-3">1-3 years</option>
                                        <option value="3-5">3-5 years</option>
                                        <option value="5+">5+ years</option>
                                    </select>
                                </div>
                                
                                <!-- Games You Sell -->
                                <div>
                                    <label for="games" class="block text-sm font-medium text-gray-300 mb-2">
                                        Games You Sell <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        id="games" 
                                        name="games" 
                                        required
                                        class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                        style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                        placeholder="Mobile Legends, PUBG Mobile, etc."
                                    >
                                    <p class="mt-1 text-xs text-gray-500">List the games you plan to sell accounts for</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Additional Information Section -->
                        <div class="mb-8">
                            <h2 class="text-xl font-semibold text-white mb-6 flex items-center">
                                <i class="fa-solid fa-info-circle mr-3 text-red-600"></i>
                                Additional Information
                            </h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Where do you prefer -->
                                <div>
                                    <label for="preferred_location" class="block text-sm font-medium text-gray-300 mb-2">
                                        Where do you prefer? <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        id="preferred_location" 
                                        name="preferred_location" 
                                        required
                                        class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                        style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                        placeholder="e.g., Online marketplace, Direct sales, etc."
                                    >
                                </div>
                                
                                <!-- How Many Accounts -->
                                <div>
                                    <label for="account_count" class="block text-sm font-medium text-gray-300 mb-2">
                                        How many accounts do you plan to list? <span class="text-red-500">*</span>
                                    </label>
                                    <select 
                                        id="account_count" 
                                        name="account_count" 
                                        required
                                        class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                        style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                    >
                                        <option value="">Select range</option>
                                        <option value="1-10">1-10 accounts</option>
                                        <option value="10-50">10-50 accounts</option>
                                        <option value="50-100">50-100 accounts</option>
                                        <option value="100+">100+ accounts</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Terms and Conditions -->
                        <div class="mb-8">
                            <div class="flex items-start">
                                <input 
                                    type="checkbox" 
                                    id="terms" 
                                    name="terms" 
                                    required
                                    class="mt-1 w-4 h-4 rounded border-gray-600 text-red-600 focus:ring-red-500 focus:ring-2" 
                                    style="background-color: #1b1a1e; border-color: #2d2c31;"
                                >
                                <label for="terms" class="ml-3 text-sm text-gray-300">
                                    I agree to the <a href="#" class="text-red-600 hover:text-red-500 underline">Terms of Service</a> and <a href="#" class="text-red-600 hover:text-red-500 underline">Privacy Policy</a> <span class="text-red-500">*</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="flex justify-end gap-4">
                            <button 
                                type="button" 
                                onclick="window.location.href='{{ route('home') }}'"
                                class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-3 sm:py-2.5 px-6 text-sm rounded-md ring-1 ring-secondary-ring text-gray-300 hover:text-white hover:bg-gray-800/50"
                                style="background-color: rgba(14, 16, 21, 0.5); border-color: #2d2c31;"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit"
                                class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap bg-red-600 hover:bg-red-700 text-white shadow-sm focus:outline-red-600 py-3 sm:py-2.5 px-8 text-sm rounded-md"
                            >
                                <span x-show="!submitted">Submit Application</span>
                                <span x-show="submitted" x-cloak>
                                    <i class="fa-solid fa-spinner fa-spin mr-2"></i> Submitting...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Benefits Section -->
            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="rounded-xl p-6" style="background-color: rgba(14, 16, 21, 0.75); border: 1px solid #2d2c31; backdrop-blur-md;">
                    <div class="flex items-center justify-center w-12 h-12 rounded-full mb-4" style="background-color: rgba(220, 38, 38, 0.1);">
                        <i class="fa-solid fa-users text-2xl text-red-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Large Customer Base</h3>
                    <p class="text-sm text-gray-400">Access thousands of potential buyers looking for gaming accounts.</p>
                </div>
                
                <div class="rounded-xl p-6" style="background-color: rgba(14, 16, 21, 0.75); border: 1px solid #2d2c31; backdrop-blur-md;">
                    <div class="flex items-center justify-center w-12 h-12 rounded-full mb-4" style="background-color: rgba(220, 38, 38, 0.1);">
                        <i class="fa-solid fa-shield-halved text-2xl text-red-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Secure Platform</h3>
                    <p class="text-sm text-gray-400">Safe and secure transactions with built-in protection for both buyers and sellers.</p>
                </div>
                
                <div class="rounded-xl p-6" style="background-color: rgba(14, 16, 21, 0.75); border: 1px solid #2d2c31; backdrop-blur-md;">
                    <div class="flex items-center justify-center w-12 h-12 rounded-full mb-4" style="background-color: rgba(220, 38, 38, 0.1);">
                        <i class="fa-solid fa-chart-line text-2xl text-red-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Grow Your Business</h3>
                    <p class="text-sm text-gray-400">Tools and analytics to help you manage and grow your account sales.</p>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/libphonenumber-js@1.10.58/bundle/libphonenumber-js.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const countrySelect = document.getElementById('country');
            const phoneInput = document.getElementById('phone');
            
            // Country code to country mapping
            const codeToCountry = {
                '+1': ['US', 'CA'],
                '+44': 'GB',
                '+33': 'FR',
                '+49': 'DE',
                '+39': 'IT',
                '+34': 'ES',
                '+31': 'NL',
                '+32': 'BE',
                '+41': 'CH',
                '+43': 'AT',
                '+46': 'SE',
                '+47': 'NO',
                '+45': 'DK',
                '+358': 'FI',
                '+48': 'PL',
                '+351': 'PT',
                '+353': 'IE',
                '+30': 'GR',
                '+7': 'RU',
                '+81': 'JP',
                '+82': 'KR',
                '+86': 'CN',
                '+91': 'IN',
                '+61': 'AU',
                '+64': 'NZ',
                '+55': 'BR',
                '+52': 'MX',
                '+54': 'AR',
                '+27': 'ZA',
                '+971': 'AE',
                '+966': 'SA',
                '+20': 'EG',
                '+90': 'TR',
                '+65': 'SG',
                '+60': 'MY',
                '+66': 'TH',
                '+84': 'VN',
                '+62': 'ID',
                '+63': 'PH'
            };
            
            // Function to detect country from phone number
            function detectCountryFromPhone(phoneNumber) {
                if (!phoneNumber || phoneNumber.trim() === '') {
                    return null;
                }
                
                // Clean the phone number (remove spaces, dashes, parentheses)
                const cleaned = phoneNumber.replace(/\D/g, '');
                
                if (cleaned.length < 3) {
                    return null;
                }
                
                // Try using libphonenumber-js if available
                if (typeof libphonenumber !== 'undefined') {
                    try {
                        // Try parsing with different formats
                        const formats = [
                            phoneNumber,
                            '+' + cleaned,
                            '+' + phoneNumber.replace(/\D/g, '')
                        ];
                        
                        for (const format of formats) {
                            try {
                                const parsed = libphonenumber.parsePhoneNumber(format);
                                if (parsed && parsed.country) {
                                    return {
                                        country: parsed.country
                                    };
                                }
                            } catch (e) {
                                continue;
                            }
                        }
                    } catch (e) {
                        // Fall through to pattern matching
                    }
                }
                
                // Fallback: Pattern matching based on country codes
                // Check if number starts with a known country code
                for (const [code, countries] of Object.entries(codeToCountry)) {
                    const codeDigits = code.replace('+', '');
                    
                    // Check if cleaned number starts with this country code
                    if (cleaned.startsWith(codeDigits)) {
                        const countryList = Array.isArray(countries) ? countries : [countries];
                        return {
                            country: countryList[0] // Default to first country if multiple
                        };
                    }
                }
                
                return null;
            }
            
            // Detect country when phone number is entered
            if (phoneInput && countrySelect) {
                let detectionTimeout;
                
                phoneInput.addEventListener('input', function() {
                    const phoneValue = this.value;
                    
                    // Clear previous timeout
                    clearTimeout(detectionTimeout);
                    
                    // Wait for user to finish typing (500ms delay)
                    detectionTimeout = setTimeout(function() {
                        const detected = detectCountryFromPhone(phoneValue);
                        
                        if (detected && detected.country) {
                            // Update country dropdown
                            const countryOption = countrySelect.querySelector(`option[value="${detected.country}"]`);
                            if (countryOption) {
                                countrySelect.value = detected.country;
                            }
                        }
                    }, 500);
                });
                
                // Also detect on paste
                phoneInput.addEventListener('paste', function() {
                    setTimeout(function() {
                        const phoneValue = phoneInput.value;
                        const detected = detectCountryFromPhone(phoneValue);
                        
                        if (detected && detected.country) {
                            const countryOption = countrySelect.querySelector(`option[value="${detected.country}"]`);
                            if (countryOption) {
                                countrySelect.value = detected.country;
                            }
                        }
                    }, 100);
                });
            }
        });
    </script>
    @endpush
@endsection

