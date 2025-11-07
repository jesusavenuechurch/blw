@extends('layouts.app')

@section('title', 'Partner Registration')

@section('head')
<style>
    .input-focus {
        transition: all 0.3s ease;
    }
    
    .input-focus:focus {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(102, 126, 234, 0.3);
    }

    .hidden-field {
        display: none;
    }
</style>
@endsection

@section('content')
<div class="container mx-auto px-4 max-w-5xl py-8">
    <!-- Error Message -->
    @if(session('error'))
        <div class="bg-white rounded-2xl shadow-2xl p-4 mb-6 border-l-4 border-red-500">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="font-bold text-red-800 text-lg">Error!</p>
                    <p class="text-red-700 mt-1">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Validation Errors -->
    @if($errors->any())
        <div class="bg-white rounded-2xl shadow-2xl p-4 mb-6 border-l-4 border-red-500">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="font-bold text-red-800 text-lg">Please fix the following errors:</p>
                    <ul class="list-disc list-inside text-red-700 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Info Banner -->
    <div class="bg-white rounded-2xl shadow-lg p-4 mb-6 border-l-4 border-blue-500">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-gray-700">
                    <strong>Registering for:</strong> {{ $partner->email }}
                </p>
                <p class="text-sm text-gray-600 mt-1">This link can only be used once. Please complete your registration below.</p>
            </div>
        </div>
    </div>

    <!-- Registration Form Card -->
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-purple-600 via-blue-500 to-indigo-600 p-8 text-white text-center">
            <svg class="w-20 h-20 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
            </svg>
            <h1 class="text-4xl font-extrabold mb-2">Complete Your Registration</h1>
            <p class="text-purple-100 text-lg">Angel Lounges Guest Management and Access Control Platform</p>
        </div>

        <!-- Form Content -->
        <div class="p-8 md:p-12">
            <form method="POST" action="{{ route('partner.store', $token) }}" class="space-y-8">
                @csrf

                <!-- Personal Information -->
                <div>
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-blue-500 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Personal Information</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Title <span class="text-red-500">*</span>
                            </label>
                            <select name="title" required class="w-full border-2 border-gray-300 rounded-xl px-4 py-3.5 input-focus focus:border-purple-500 @error('title') border-red-500 @enderror">
                                <option value="">Select Title</option>
                                <option value="Brother" {{ old('title', $partner->title) == 'Brother' ? 'selected' : '' }}>Brother</option>
                                <option value="Sister" {{ old('title', $partner->title) == 'Sister' ? 'selected' : '' }}>Sister</option>
                                <option value="Deacon" {{ old('title', $partner->title) == 'Deacon' ? 'selected' : '' }}>Deacon</option>
                                <option value="Deaconess" {{ old('title', $partner->title) == 'Deaconess' ? 'selected' : '' }}>Deaconess</option>
                                <option value="Pastor" {{ old('title', $partner->title) == 'Pastor' ? 'selected' : '' }}>Pastor</option>
                            </select>
                            @error('title')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Designation <span class="text-red-500">*</span>
                            </label>
                            <select name="designation" required class="w-full border-2 border-gray-300 rounded-xl px-4 py-3.5 input-focus focus:border-purple-500 @error('designation') border-red-500 @enderror">
                                <option value="">Select Designation</option>
                                <option value="Non-Pastoring" {{ old('designation', $partner->designation) == 'Non-Pastoring' ? 'selected' : '' }}>Non-Pastoring</option>
                                <option value="BLW Group Secretary" {{ old('designation', $partner->designation) == 'BLW Group Secretary' ? 'selected' : '' }}>BLW Group Secretary</option>
                                <option value="BLW Zonal Secretary" {{ old('designation', $partner->designation) == 'BLW Zonal Secretary' ? 'selected' : '' }}>BLW Zonal Secretary</option>
                                <option value="BLW Regional Secretary" {{ old('designation', $partner->designation) == 'BLW Regional Secretary' ? 'selected' : '' }}>BLW Regional Secretary</option>
                                <option value="Church Pastor" {{ old('designation', $partner->designation) == 'Church Pastor' ? 'selected' : '' }}>Church Pastor</option>
                                <option value="Sub-Group Pastor" {{ old('designation', $partner->designation) == 'Sub-Group Pastor' ? 'selected' : '' }}>Sub-Group Pastor</option>
                                <option value="Group Pastor" {{ old('designation', $partner->designation) == 'Group Pastor' ? 'selected' : '' }}>Group Pastor</option>
                                <option value="Asst. Zonal Pastor" {{ old('designation', $partner->designation) == 'Asst. Zonal Pastor' ? 'selected' : '' }}>Asst. Zonal Pastor</option>
                                <option value="Zonal Pastor" {{ old('designation', $partner->designation) == 'Zonal Pastor' ? 'selected' : '' }}>Zonal Pastor</option>
                                <option value="Zonal Director" {{ old('designation', $partner->designation) == 'Zonal Director' ? 'selected' : '' }}>Zonal Director</option>
                                <option value="Regional Pastor" {{ old('designation', $partner->designation) == 'Regional Pastor' ? 'selected' : '' }}>Regional Pastor</option>
                            </select>
                            @error('designation')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="full_name" value="{{ old('full_name', $partner->full_name) }}" required placeholder="John Doe"
                                   class="w-full border-2 border-gray-300 rounded-xl px-4 py-3.5 input-focus focus:border-purple-500 @error('full_name') border-red-500 @enderror">
                            @error('full_name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                            <input type="email" value="{{ $partner->email }}" readonly class="w-full border-2 border-gray-200 bg-gray-100 rounded-xl px-4 py-3.5 cursor-not-allowed">
                            <p class="text-xs text-gray-500 mt-1">Email cannot be changed</p>
                        </div>

<div>
    <label class="block text-sm font-semibold text-gray-700 mb-2">
        Phone Number <span class="text-red-500">*</span>
    </label>
    <div class="flex gap-2">
        <div class="relative w-40">
            <input type="text" 
                   id="country_code_search" 
                   placeholder="Search..."
                   class="w-full border-2 border-gray-300 rounded-xl px-3 py-3.5 input-focus focus:border-purple-500 text-sm"
                   autocomplete="off">
            <select id="country_code_selector" 
                    size="1"
                    class="absolute top-full left-0 right-0 mt-1 border-2 border-gray-300 rounded-xl bg-white shadow-lg max-h-60 overflow-y-auto hidden z-10">
                <option value="+93">🇦🇫 Afghanistan +93</option>
                <option value="+355">🇦🇱 Albania +355</option>
                <option value="+213">🇩🇿 Algeria +213</option>
                <option value="+376">🇦🇩 Andorra +376</option>
                <option value="+244">🇦🇴 Angola +244</option>
                <option value="+54">🇦🇷 Argentina +54</option>
                <option value="+374">🇦🇲 Armenia +374</option>
                <option value="+61">🇦🇺 Australia +61</option>
                <option value="+43">🇦🇹 Austria +43</option>
                <option value="+994">🇦🇿 Azerbaijan +994</option>
                <option value="+973">🇧🇭 Bahrain +973</option>
                <option value="+880">🇧🇩 Bangladesh +880</option>
                <option value="+375">🇧🇾 Belarus +375</option>
                <option value="+32">🇧🇪 Belgium +32</option>
                <option value="+229">🇧🇯 Benin +229</option>
                <option value="+591">🇧🇴 Bolivia +591</option>
                <option value="+387">🇧🇦 Bosnia +387</option>
                <option value="+267">🇧🇼 Botswana +267</option>
                <option value="+55">🇧🇷 Brazil +55</option>
                <option value="+673">🇧🇳 Brunei +673</option>
                <option value="+359">🇧🇬 Bulgaria +359</option>
                <option value="+226">🇧🇫 Burkina Faso +226</option>
                <option value="+257">🇧🇮 Burundi +257</option>
                <option value="+855">🇰🇭 Cambodia +855</option>
                <option value="+237">🇨🇲 Cameroon +237</option>
                <option value="+1">🇨🇦 Canada +1</option>
                <option value="+238">🇨🇻 Cape Verde +238</option>
                <option value="+236">🇨🇫 C.A. Republic +236</option>
                <option value="+235">🇹🇩 Chad +235</option>
                <option value="+56">🇨🇱 Chile +56</option>
                <option value="+86">🇨🇳 China +86</option>
                <option value="+57">🇨🇴 Colombia +57</option>
                <option value="+243">🇨🇩 Congo (DRC) +243</option>
                <option value="+242">🇨🇬 Congo +242</option>
                <option value="+506">🇨🇷 Costa Rica +506</option>
                <option value="+385">🇭🇷 Croatia +385</option>
                <option value="+53">🇨🇺 Cuba +53</option>
                <option value="+357">🇨🇾 Cyprus +357</option>
                <option value="+420">🇨🇿 Czech Rep. +420</option>
                <option value="+45">🇩🇰 Denmark +45</option>
                <option value="+253">🇩🇯 Djibouti +253</option>
                <option value="+593">🇪🇨 Ecuador +593</option>
                <option value="+20">🇪🇬 Egypt +20</option>
                <option value="+503">🇸🇻 El Salvador +503</option>
                <option value="+240">🇬🇶 Eq. Guinea +240</option>
                <option value="+291">🇪🇷 Eritrea +291</option>
                <option value="+372">🇪🇪 Estonia +372</option>
                <option value="+251">🇪🇹 Ethiopia +251</option>
                <option value="+358">🇫🇮 Finland +358</option>
                <option value="+33">🇫🇷 France +33</option>
                <option value="+241">🇬🇦 Gabon +241</option>
                <option value="+220">🇬🇲 Gambia +220</option>
                <option value="+995">🇬🇪 Georgia +995</option>
                <option value="+49">🇩🇪 Germany +49</option>
                <option value="+233">🇬🇭 Ghana +233</option>
                <option value="+30">🇬🇷 Greece +30</option>
                <option value="+502">🇬🇹 Guatemala +502</option>
                <option value="+224">🇬🇳 Guinea +224</option>
                <option value="+245">🇬🇼 Guinea-Bissau +245</option>
                <option value="+509">🇭🇹 Haiti +509</option>
                <option value="+504">🇭🇳 Honduras +504</option>
                <option value="+852">🇭🇰 Hong Kong +852</option>
                <option value="+36">🇭🇺 Hungary +36</option>
                <option value="+354">🇮🇸 Iceland +354</option>
                <option value="+91">🇮🇳 India +91</option>
                <option value="+62">🇮🇩 Indonesia +62</option>
                <option value="+98">🇮🇷 Iran +98</option>
                <option value="+964">🇮🇶 Iraq +964</option>
                <option value="+353">🇮🇪 Ireland +353</option>
                <option value="+972">🇮🇱 Israel +972</option>
                <option value="+39">🇮🇹 Italy +39</option>
                <option value="+225">🇨🇮 Ivory Coast +225</option>
                <option value="+81">🇯🇵 Japan +81</option>
                <option value="+962">🇯🇴 Jordan +962</option>
                <option value="+7">🇰🇿 Kazakhstan +7</option>
                <option value="+254">🇰🇪 Kenya +254</option>
                <option value="+965">🇰🇼 Kuwait +965</option>
                <option value="+996">🇰🇬 Kyrgyzstan +996</option>
                <option value="+371">🇱🇻 Latvia +371</option>
                <option value="+961">🇱🇧 Lebanon +961</option>
                <option value="+266">🇱🇸 Lesotho +266</option>
                <option value="+231">🇱🇷 Liberia +231</option>
                <option value="+218">🇱🇾 Libya +218</option>
                <option value="+370">🇱🇹 Lithuania +370</option>
                <option value="+352">🇱🇺 Luxembourg +352</option>
                <option value="+261">🇲🇬 Madagascar +261</option>
                <option value="+265">🇲🇼 Malawi +265</option>
                <option value="+60">🇲🇾 Malaysia +60</option>
                <option value="+223">🇲🇱 Mali +223</option>
                <option value="+356">🇲🇹 Malta +356</option>
                <option value="+222">🇲🇷 Mauritania +222</option>
                <option value="+230">🇲🇺 Mauritius +230</option>
                <option value="+52">🇲🇽 Mexico +52</option>
                <option value="+373">🇲🇩 Moldova +373</option>
                <option value="+377">🇲🇨 Monaco +377</option>
                <option value="+976">🇲🇳 Mongolia +976</option>
                <option value="+382">🇲🇪 Montenegro +382</option>
                <option value="+212">🇲🇦 Morocco +212</option>
                <option value="+258">🇲🇿 Mozambique +258</option>
                <option value="+95">🇲🇲 Myanmar +95</option>
                <option value="+264">🇳🇦 Namibia +264</option>
                <option value="+977">🇳🇵 Nepal +977</option>
                <option value="+31">🇳🇱 Netherlands +31</option>
                <option value="+64">🇳🇿 New Zealand +64</option>
                <option value="+505">🇳🇮 Nicaragua +505</option>
                <option value="+227">🇳🇪 Niger +227</option>
                <option value="+234" selected>🇳🇬 Nigeria +234</option>
                <option value="+850">🇰🇵 North Korea +850</option>
                <option value="+47">🇳🇴 Norway +47</option>
                <option value="+968">🇴🇲 Oman +968</option>
                <option value="+92">🇵🇰 Pakistan +92</option>
                <option value="+970">🇵🇸 Palestine +970</option>
                <option value="+507">🇵🇦 Panama +507</option>
                <option value="+675">🇵🇬 Papua N.G. +675</option>
                <option value="+595">🇵🇾 Paraguay +595</option>
                <option value="+51">🇵🇪 Peru +51</option>
                <option value="+63">🇵🇭 Philippines +63</option>
                <option value="+48">🇵🇱 Poland +48</option>
                <option value="+351">🇵🇹 Portugal +351</option>
                <option value="+974">🇶🇦 Qatar +974</option>
                <option value="+40">🇷🇴 Romania +40</option>
                <option value="+7">🇷🇺 Russia +7</option>
                <option value="+250">🇷🇼 Rwanda +250</option>
                <option value="+966">🇸🇦 Saudi Arabia +966</option>
                <option value="+221">🇸🇳 Senegal +221</option>
                <option value="+381">🇷🇸 Serbia +381</option>
                <option value="+248">🇸🇨 Seychelles +248</option>
                <option value="+232">🇸🇱 Sierra Leone +232</option>
                <option value="+65">🇸🇬 Singapore +65</option>
                <option value="+421">🇸🇰 Slovakia +421</option>
                <option value="+386">🇸🇮 Slovenia +386</option>
                <option value="+252">🇸🇴 Somalia +252</option>
                <option value="+27">🇿🇦 South Africa +27</option>
                <option value="+82">🇰🇷 South Korea +82</option>
                <option value="+211">🇸🇸 South Sudan +211</option>
                <option value="+34">🇪🇸 Spain +34</option>
                <option value="+94">🇱🇰 Sri Lanka +94</option>
                <option value="+249">🇸🇩 Sudan +249</option>
                <option value="+597">🇸🇷 Suriname +597</option>
                <option value="+268">🇸🇿 Eswatini +268</option>
                <option value="+46">🇸🇪 Sweden +46</option>
                <option value="+41">🇨🇭 Switzerland +41</option>
                <option value="+963">🇸🇾 Syria +963</option>
                <option value="+886">🇹🇼 Taiwan +886</option>
                <option value="+992">🇹🇯 Tajikistan +992</option>
                <option value="+255">🇹🇿 Tanzania +255</option>
                <option value="+66">🇹🇭 Thailand +66</option>
                <option value="+228">🇹🇬 Togo +228</option>
                <option value="+216">🇹🇳 Tunisia +216</option>
                <option value="+90">🇹🇷 Turkey +90</option>
                <option value="+993">🇹🇲 Turkmenistan +993</option>
                <option value="+256">🇺🇬 Uganda +256</option>
                <option value="+380">🇺🇦 Ukraine +380</option>
                <option value="+971">🇦🇪 UAE +971</option>
                <option value="+44">🇬🇧 UK +44</option>
                <option value="+1">🇺🇸 USA +1</option>
                <option value="+598">🇺🇾 Uruguay +598</option>
                <option value="+998">🇺🇿 Uzbekistan +998</option>
                <option value="+58">🇻🇪 Venezuela +58</option>
                <option value="+84">🇻🇳 Vietnam +84</option>
                <option value="+967">🇾🇪 Yemen +967</option>
                <option value="+260">🇿🇲 Zambia +260</option>
                <option value="+263">🇿🇼 Zimbabwe +263</option>
                <option value="custom">➕ Enter manually...</option>
            </select>
        </div>
        <input type="tel" id="phone_input" name="phone" value="{{ old('phone', $partner->phone) }}" 
               required 
               placeholder="1234567890"
               class="flex-1 border-2 border-gray-300 rounded-xl px-4 py-3.5 input-focus focus:border-purple-500 @error('phone') border-red-500 @enderror">
    </div>
    <p class="text-xs text-gray-500 mt-1">Search and select country code, or click "Enter manually" if not found</p>
    @error('phone')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
</div>

<!-- KC Handle (Required) -->
<div>
    <label class="block text-sm font-semibold text-gray-700 mb-2">
        KC Handle <span class="text-red-500">*</span>
    </label>
    <div class="relative">
        <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 font-semibold">@</span>
        <input type="text" name="kc_handle" value="{{ old('kc_handle', $partner->kc_handle) }}" 
               required
               placeholder="username"
               class="w-full border-2 border-gray-300 rounded-xl pl-10 pr-4 py-3.5 input-focus focus:border-purple-500 @error('kc_handle') border-red-500 @enderror">
    </div>
    <p class="text-xs text-gray-500 mt-1">Your KingsChat handle (required)</p>
    @error('kc_handle')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const searchInput = document.getElementById('country_code_search');
    const dropdown = document.getElementById('country_code_selector');
    const phoneInput = document.getElementById('phone_input');
    let selectedCode = '+234'; // Default Nigeria
    
    // Extract existing phone if it has country code
    if (phoneInput.value && phoneInput.value.startsWith('+')) {
        const match = phoneInput.value.match(/^(\+\d+)(.*)$/);
        if (match) {
            selectedCode = match[1];
            phoneInput.value = match[2];
            searchInput.value = selectedCode;
            
            // Find and select in dropdown
            for (let option of dropdown.options) {
                if (option.value === selectedCode) {
                    option.selected = true;
                    searchInput.value = option.text.split('+')[0].trim() + ' ' + selectedCode;
                    break;
                }
            }
        }
    } else {
        searchInput.value = '🇳🇬 Nigeria +234';
    }
    
    // Show dropdown on search focus
    searchInput.addEventListener('focus', function() {
        dropdown.classList.remove('hidden');
        dropdown.size = 8;
    });
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        let hasVisibleOption = false;
        
        for (let option of dropdown.options) {
            const text = option.text.toLowerCase();
            if (text.includes(searchTerm) || searchTerm === '') {
                option.style.display = '';
                hasVisibleOption = true;
            } else {
                option.style.display = 'none';
            }
        }
        
        dropdown.classList.remove('hidden');
    });
    
    // Select from dropdown
    dropdown.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        
        if (selected.value === 'custom') {
            // Manual entry
            const manualCode = prompt('Enter country code (e.g., +234):', '+');
            if (manualCode && manualCode.startsWith('+')) {
                selectedCode = manualCode;
                searchInput.value = manualCode;
            }
        } else {
            selectedCode = selected.value;
            searchInput.value = selected.text;
        }
        
        dropdown.classList.add('hidden');
        phoneInput.focus();
    });
    
    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
    
    // Combine country code with phone on submit
    form.addEventListener('submit', function(e) {
        const phoneNumber = phoneInput.value.trim();
        
        // Only add country code if phone doesn't already have one
        if (!phoneNumber.startsWith('+')) {
            phoneInput.value = selectedCode + phoneNumber;
        }
    });
});
</script>
                    </div>
                </div>

                <!-- Church Information -->
                <div>
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Church Information</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Region</label>
                            <input type="text" name="region" value="{{ old('region', $partner->region) }}" placeholder="Enter your region"
                                   class="w-full border-2 border-gray-300 rounded-xl px-4 py-3.5 input-focus focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Zone</label>
                            <input type="text" name="zone" value="{{ old('zone', $partner->zone) }}" placeholder="Enter your zone"
                                   class="w-full border-2 border-gray-300 rounded-xl px-4 py-3.5 input-focus focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Group</label>
                            <input type="text" name="group" value="{{ old('group', $partner->group) }}" placeholder="Enter your group"
                                   class="w-full border-2 border-gray-300 rounded-xl px-4 py-3.5 input-focus focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Church</label>
                            <input type="text" name="church" value="{{ old('church', $partner->church) }}" placeholder="Enter your church name"
                                   class="w-full border-2 border-gray-300 rounded-xl px-4 py-3.5 input-focus focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Partnership Details -->
                <div>
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Partnership Details</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Partnership Tier <span class="ml-2 text-xs font-normal text-gray-500">(Assigned by admin)</span>
                            </label>
                            <div class="w-full border-2 border-gray-200 bg-gray-100 rounded-xl px-4 py-3.5 font-semibold">
                                @if($partner->tier === 'ruby')
                                    <span class="text-red-600">💎 Ruby Partner</span>
                                @elseif($partner->tier === 'silver')
                                    <span class="text-gray-600">🥈 Silver Partner</span>
                                @elseif($partner->tier === 'gold')
                                    <span class="text-yellow-600">🥇 Gold Partner</span>
                                @elseif($partner->tier === 'diamond')
                                    <span class="text-purple-600">💠 Diamond Partner</span>
                                @elseif($partner->tier === 'as_one_man')
                                    <span class="text-indigo-600">💞 As One Man Partner</span>
                                @elseif($partner->tier === 'top_individual')
                                    <span class="text-blue-600">⭐ Top Individual Partner</span>
                                @else
                                    <span class="text-gray-600">Not Assigned</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <label class="relative flex items-start p-5 bg-gradient-to-r from-purple-50 to-blue-50 rounded-xl cursor-pointer hover:shadow-lg transition duration-300">
                            <input type="checkbox" name="will_attend_ippc" id="will_attend_ippc" value="1" 
                                   {{ old('will_attend_ippc', $partner->will_attend_ippc) ? 'checked' : '' }}
                                   onchange="toggleIPPCFields()"
                                   class="w-5 h-5 text-purple-600 rounded mt-0.5">
                            <div class="ml-4">
                                <span class="font-semibold text-gray-800 block">Will you attend IPPC 2025?</span>
                                <span class="text-sm text-gray-600">Check if you will attend the International Partners and Pastors Conference</span>
                            </div>
                        </label>

                        <div id="exhibition_field" class="hidden-field">
                            <label class="relative flex items-start p-5 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl cursor-pointer hover:shadow-lg transition duration-300">
                                <input type="checkbox" name="will_be_at_exhibition" id="will_be_at_exhibition" value="1" 
                                       {{ old('will_be_at_exhibition', $partner->will_be_at_exhibition) ? 'checked' : '' }}
                                       onchange="toggleIPPCFields()"
                                       class="w-5 h-5 text-blue-600 rounded mt-0.5">
                                <div class="ml-4">
                                    <span class="font-semibold text-gray-800 block">Will you be our honoured Guest at the Angel Lounge?</span>
                                    <span class="text-sm text-gray-600">Check if you plan to visit the Angel Lounge exhibition</span>
                                </div>
                            </label>
                        </div>

                        <div id="delivery_field" class="hidden-field">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                How should we deliver your ROR gifts? <span class="text-red-500">*</span>
                            </label>
                            <textarea name="delivery_method" id="delivery_method" rows="3" placeholder="Please provide the name and contact information for the liaison person..."
                                      class="w-full border-2 border-gray-300 rounded-xl px-4 py-3.5 input-focus focus:border-indigo-500">{{ old('delivery_method', $partner->delivery_method) }}</textarea>
                            <p class="text-sm text-gray-500 mt-1">Required if not attending both IPPC and Angel Lounge</p>
                        </div>
                    </div>
                </div>

                <!-- Spouse Information -->
                <div>
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-purple-500 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Spouse Information</h2>
                    </div>

                    <div class="mb-6">
                        <label class="relative flex items-start p-5 bg-gradient-to-r from-pink-50 to-purple-50 rounded-xl cursor-pointer hover:shadow-lg transition duration-300">
                            <input type="checkbox" name="coming_with_spouse" id="coming_with_spouse" value="1" 
                                   {{ old('coming_with_spouse', $partner->coming_with_spouse) ? 'checked' : '' }}
                                   onchange="toggleSpouseFields()"
                                   class="w-5 h-5 text-pink-600 rounded mt-0.5">
                            <div class="ml-4">
                                <span class="font-semibold text-gray-800 block">Coming with spouse?</span>
                                <span class="text-sm text-gray-600">Check if your spouse will be joining you</span>
                            </div>
                        </label>
                    </div>

                    <div id="spouse_fields" class="hidden-field">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Spouse Title <span class="text-red-500">*</span>
                                </label>
                                <select name="spouse_title" id="spouse_title" class="w-full border-2 border-gray-300 rounded-xl px-4 py-3.5 input-focus focus:border-pink-500">
                                    <option value="">Select Title</option>
                                    <option value="Brother" {{ old('spouse_title', $partner->spouse_title) == 'Brother' ? 'selected' : '' }}>Brother</option>
                                    <option value="Sister" {{ old('spouse_title', $partner->spouse_title) == 'Sister' ? 'selected' : '' }}>Sister</option>
                                    <option value="Deacon" {{ old('spouse_title', $partner->spouse_title) == 'Deacon' ? 'selected' : '' }}>Deacon</option>
                                    <option value="Deaconess" {{ old('spouse_title', $partner->spouse_title) == 'Deaconess' ? 'selected' : '' }}>Deaconess</option>
                                    <option value="Pastor" {{ old('spouse_title', $partner->spouse_title) == 'Pastor' ? 'selected' : '' }}>Pastor</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Spouse Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="spouse_name" id="spouse_name" value="{{ old('spouse_name', $partner->spouse_name) }}" placeholder="Full name"
                                       class="w-full border-2 border-gray-300 rounded-xl px-4 py-3.5 input-focus focus:border-pink-500">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Spouse KC Handle</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 font-semibold">@</span>
                                    <input type="text" name="spouse_kc_handle" value="{{ old('spouse_kc_handle', $partner->spouse_kc_handle) }}" placeholder="username"
                                           class="w-full border-2 border-gray-300 rounded-xl pl-10 pr-4 py-3.5 input-focus focus:border-pink-500">
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Spouse's KingsChat handle</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-6">
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-purple-600 via-blue-500 to-indigo-600 text-white px-8 py-4 rounded-xl font-bold text-lg hover:shadow-2xl transform hover:scale-105 transition duration-300">
                        <span class="flex items-center justify-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Complete Registration
                        </span>
                    </button>
                    <p class="text-center text-sm text-gray-500 mt-4">
                        By submitting, you confirm that all information provided is accurate.
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleIPPCFields() {
        const attendingIPPC = document.getElementById('will_attend_ippc').checked;
        const attendingExhibition = document.getElementById('will_be_at_exhibition').checked;
        const exhibitionField = document.getElementById('exhibition_field');
        const deliveryField = document.getElementById('delivery_field');
        const deliveryTextarea = document.getElementById('delivery_method');
        
        if (attendingIPPC) {
            exhibitionField.classList.remove('hidden-field');
            
            // Only hide delivery if attending BOTH IPPC and exhibition
            if (attendingExhibition) {
                deliveryField.classList.add('hidden-field');
                if (deliveryTextarea) deliveryTextarea.required = false;
            } else {
                deliveryField.classList.remove('hidden-field');
                if (deliveryTextarea) deliveryTextarea.required = true;
            }
        } else {
            exhibitionField.classList.add('hidden-field');
            deliveryField.classList.remove('hidden-field');
            if (deliveryTextarea) deliveryTextarea.required = true;
        }
    }

    function toggleSpouseFields() {
        const withSpouse = document.getElementById('coming_with_spouse').checked;
        const spouseFields = document.getElementById('spouse_fields');
        const spouseTitle = document.getElementById('spouse_title');
        const spouseName = document.getElementById('spouse_name');
        
        if (withSpouse) {
            spouseFields.classList.remove('hidden-field');
            if (spouseTitle) spouseTitle.required = true;
            if (spouseName) spouseName.required = true;
        } else {
            spouseFields.classList.add('hidden-field');
            if (spouseTitle) spouseTitle.required = false;
            if (spouseName) spouseName.required = false;
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleIPPCFields();
        toggleSpouseFields();
        
        // Add event listener to exhibition checkbox
        const exhibitionCheckbox = document.getElementById('will_be_at_exhibition');
        if (exhibitionCheckbox) {
            exhibitionCheckbox.addEventListener('change', toggleIPPCFields);
        }
    });
</script>
@endsection