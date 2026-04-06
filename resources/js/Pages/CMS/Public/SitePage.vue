<template>
    <!-- ══ SEO Head ══ -->
    <Head>
        <title>{{ seo.title }}</title>
        <meta name="description" :content="seo.description" />
        <meta name="keywords" :content="seo.keywords" />
        <link rel="canonical" :href="seo.canonical" />

        <!-- Open Graph -->
        <meta property="og:type" content="website" />
        <meta property="og:title" :content="seo.title" />
        <meta property="og:description" :content="seo.description" />
        <meta property="og:url" :content="seo.canonical" />
        <meta v-if="seo.og_image" property="og:image" :content="seo.og_image" />
        <meta property="og:site_name" :content="site.name" />

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="seo.title" />
        <meta name="twitter:description" :content="seo.description" />
        <meta v-if="seo.og_image" name="twitter:image" :content="seo.og_image" />

        <!-- PWA Manifest -->
        <link v-if="pwa" rel="manifest" href="/manifest.json" />
        <meta v-if="pwa" name="theme-color" :content="pwa.theme_color || '#4f46e5'" />

        <!-- JSON-LD Schema (Safe Injection) -->
        <component :is="'script'" v-if="seo.schema" type="application/ld+json" v-html="seo.schema" />
    </Head>

    <div :style="themeVars" class="site-root min-h-screen flex flex-col bg-gray-100/50 text-slate-900" :data-theme="site.name">
        <!-- ══ Flipkart-Style Header (Indigo Theme) ══ -->
        <header class="sticky top-0 z-50 bg-emerald-500 text-white shadow-md">
            <div class="max-w-[1400px] mx-auto px-4 md:px-6 h-14 md:h-16 flex items-center justify-between gap-4 md:gap-8">
                <!-- Branding -->
                <Link href="/" class="flex items-center gap-3 shrink-0 active:scale-95 transition-transform">
                    <div class="w-8 h-8 md:w-9 md:h-9 bg-white rounded-lg flex items-center justify-center text-emerald-500 font-black text-lg md:text-xl shadow-sm">
                        {{ (site.name || 'S').charAt(0).toUpperCase() }}
                    </div>
                    <div class="hidden sm:block">
                        <h1 class="text-lg md:text-xl font-black italic tracking-tighter uppercase leading-none">{{ site.name }}</h1>
                        <p class="text-xs font-bold text-emerald-100 uppercase tracking-widest mt-0.5">Explore <span class="text-white italic">Plus</span></p>
                    </div>
                </Link>

                <!-- Wide Search (Flipkart-Style) -->
                <div class="hidden md:block flex-1 max-w-2xl relative">
                    <input v-model="searchQuery" @keyup.enter="performSearch" type="text" placeholder="Search for products, brands and more" class="w-full bg-white border-none rounded-md px-4 py-2 text-sm font-medium text-slate-900 focus:ring-0 shadow-sm placeholder:text-slate-400" />
                    <button @click="performSearch" class="absolute right-4 top-1/2 -translate-y-1/2 text-emerald-400 hover:text-emerald-600">
                        <i class="fas fa-search"></i>
                    </button>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-4 md:gap-6 shrink-0">
                    <template v-if="site.user">
                        <div class="flex items-center gap-4">
                            <span class="text-sm font-black uppercase tracking-widest text-emerald-100 hidden lg:block">Hi, {{ site.user.name.split(' ')[0] }}</span>
                            <div class="group relative">
                                <button class="w-8 h-8 md:w-8 md:h-8 rounded-full bg-emerald-500 flex items-center justify-center border border-emerald-400">
                                    <i class="fas fa-user text-sm"></i>
                                </button>
                                <!-- Dropdown (Simple) -->
                                <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-2xl py-4 hidden group-hover:block border border-gray-100 animate-fade-in-down">
                                    <p class="px-6 pb-2 mb-2 border-b border-gray-50 text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Account Hub</p>
                                    <Link href="/customer/orders" class="block px-6 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 uppercase tracking-tight italic">My Orders</Link>
                                    <Link href="/customer/wishlist" class="block px-6 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 uppercase tracking-tight italic">Wishlist</Link>
                                    <Link href="/customer/logout" method="post" as="button" class="w-full text-left px-6 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 uppercase tracking-tight italic">Logout</Link>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <Link href="/customer/login" class="bg-white text-emerald-500 px-4 md:px-8 py-1.5 rounded-sm font-black text-xs md:text-sm hover:bg-gray-100 transition-colors shadow-sm whitespace-nowrap">
                            Login
                        </Link>
                    </template>

                    <button @click="cartOpen = true" class="flex items-center gap-2 font-black text-xs md:text-sm hover:text-emerald-100 transition-colors">
                        <div class="relative">
                            <i class="fas fa-shopping-cart text-lg"></i>
                            <span v-if="cartCount > 0" class="absolute -top-2 -right-2 bg-teal-500 text-white text-xs font-black w-4 h-4 rounded-full flex items-center justify-center border border-emerald-500">
                                {{ cartCount }}
                            </span>
                        </div>
                        <span class="hidden sm:inline">Cart</span>
                    </button>
                    <Link href="/become-seller" class="hidden xl:flex items-center gap-2 font-black text-sm hover:text-emerald-100 transition-colors">
                        <i class="fas fa-store text-lg"></i>
                        <span>Become a Seller</span>
                    </Link>
                </div>
            </div>
            
            <!-- Category Navigation Strip (Compact with Subcategories) -->
            <nav class="bg-white border-b border-gray-100 relative z-40">
                <div class="max-w-[1400px] mx-auto px-4 overflow-x-auto no-scrollbar py-2 md:py-3">
                    <ul class="flex items-center justify-start sm:justify-center gap-6 md:gap-12 min-w-max">
                        <li v-for="cat in (ecom?.categories || []).filter(c => !c.parent_id)" :key="cat.id" class="group relative">
                            <Link :href="'/' + cat.slug" class="flex flex-col items-center gap-1.5 py-1">
                                <div class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500 group-hover:bg-emerald-100 transition-colors duration-300 shadow-sm border border-emerald-100">
                                    <img v-if="cat.image" :src="cat.image" class="w-full h-full object-cover rounded-full" />
                                    <i v-else class="fas fa-tag text-sm md:text-base"></i>
                                </div>
                                <span class="text-sm md:text-sm font-black text-slate-700 uppercase tracking-widest group-hover:text-emerald-600">{{ cat.name }}</span>
                            </Link>
                            
                            <!-- Subcategory Dropdown -->
                            <div v-if="(ecom?.categories || []).filter(sub => sub.parent_id === cat.id).length" class="absolute top-full left-1/2 w-48 -translate-x-1/2 pt-2 hidden group-hover:block z-50 animate-fade-in-down">
                                <div class="bg-white rounded-xl shadow-xl border border-gray-100 p-2">
                                    <ul class="space-y-0.5">
                                        <li v-for="sub in (ecom?.categories || []).filter(sub => sub.parent_id === cat.id)" :key="sub.id">
                                            <Link :href="'/' + sub.slug" class="block px-4 py-2 text-xs font-bold text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors">
                                                {{ sub.name }}
                                            </Link>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>

        <!-- ══ Content View ══ -->
        <main class="flex-1 pb-16">
            
            <!-- A. Page / Homepage View -->
            <template v-if="view_type === 'page' || view_type === 'home'">
                <template v-if="blocks && blocks.length">
                    <template v-for="block in blocks" :key="block.id">
                        <component
                            v-if="getBlockComponent(block.type)"
                            :is="getBlockComponent(block.type)"
                            :block="block"
                            :dynamicData="ecom"
                            :site="site"
                            :forms="forms"
                            @add-to-cart="addToCart"
                            @submit-form="submitForm"
                        />
                    </template>
                </template>
                <!-- Zero-Config Flipkart-Style Homepage -->
                <div v-else class="space-y-4 pt-4">
                    <!-- Hero Slider -->
                    <div class="max-w-[1400px] mx-auto px-2">
                        <section class="h-[250px] md:h-[400px] rounded-lg overflow-hidden bg-emerald-700 shadow-sm relative">
                            <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&q=80&w=1600" class="w-full h-full object-cover opacity-60" />
                            <div class="absolute inset-0 bg-gradient-to-r from-emerald-800 via-emerald-800/40 to-transparent flex items-center px-6 md:px-16">
                                <div class="max-w-xl">
                                    <h2 class="text-3xl md:text-5xl font-black text-white italic tracking-tighter leading-none mb-6">FASHION'S <br/> BIGGEST <br/> <span class="text-emerald-400 underline underline-offset-8">REVEAL</span></h2>
                                    <p class="text-lg text-emerald-100 font-medium mb-8">Upto 80% Off on Global Brands. Starting from ₹499.</p>
                                    <button class="px-10 py-4 bg-white text-emerald-500 font-black rounded-lg text-sm uppercase tracking-widest hover:bg-gray-100 transition-all shadow-xl active:scale-95">Explore All</button>
                                </div>
                            </div>
                        </section>
                    </div>

                    <!-- Horizonal Scroll Sections (Dynamic from Live Data) -->
                    <template v-for="section in [
                        { name: 'Men\'s Trendsetters', catId: 1 },
                        { name: 'Women\'s Elite', catId: 11 },
                        { name: 'Premium Footwear', catId: 18 }
                    ]" :key="section.name">
                        <section v-if="getCategoryProducts(section.catId).length" class="max-w-[1400px] mx-auto bg-white rounded-lg shadow-sm p-6">
                            <div class="flex items-center justify-between mb-6 pb-4 border-b">
                                <h3 class="text-xl font-black text-emerald-800 uppercase tracking-tighter">{{ section.name }}</h3>
                                <Link :href="'/' + getCatSlug(section.catId)" class="px-6 py-2 bg-emerald-500 text-white text-sm font-black uppercase tracking-widest rounded-md hover:bg-emerald-700 transition-all">View All</Link>
                            </div>
                            
                            <div class="flex gap-6 overflow-x-auto no-scrollbar pb-2">
                                <div v-for="prod in getCategoryProducts(section.catId)" :key="prod.id" class="w-48 shrink-0 group cursor-pointer" @click="goToProduct(prod)">
                                    <div class="aspect-[3/4] bg-gray-50 rounded-xl overflow-hidden border border-gray-100 shadow-sm mb-3 group-hover:scale-[1.02] transition-transform duration-300">
                                        <img :src="parsedFirstImage(prod.images)" class="w-full h-full object-cover" />
                                    </div>
                                    <p class="text-sm md:text-base font-black text-slate-800 uppercase tracking-tight truncate leading-none">{{ prod.name }}</p>
                                    <div class="flex items-center gap-2 mt-2">
                                        <span class="text-xs md:text-sm font-black text-emerald-500">₹{{ formatPrice(prod.price) }}</span>
                                        <span v-if="prod.mrp > prod.price" class="text-sm text-slate-400 line-through">₹{{ formatPrice(prod.mrp) }}</span>
                                    </div>
                                    <p class="text-sm font-bold text-emerald-500 uppercase mt-1">Free Delivery</p>
                                </div>
                            </div>
                        </section>
                    </template>
                    
                    <!-- Banners Grid -->
                    <section class="max-w-[1400px] mx-auto grid grid-cols-1 md:grid-cols-3 gap-4 h-[200px]">
                        <div v-for="i in 3" :key="i" class="rounded-lg overflow-hidden relative group cursor-pointer">
                            <img :src="`https://loremflickr.com/800/400/fashion,banner?lock=${100+i}`" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000" />
                            <div class="absolute inset-0 bg-emerald-800/20 group-hover:bg-emerald-800/40 transition-colors flex items-center justify-center">
                                <span class="bg-white text-emerald-800 px-4 py-2 rounded-full text-sm font-black uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-all">Shop Deal</span>
                            </div>
                        </div>
                    </section>
                </div>
            </template>

            <!-- B. Category View -->
            <div v-else-if="view_type === 'category' && category" class="pb-32">
                <div class="relative bg-white border-b border-gray-100 pt-10 pb-8 md:pt-20 md:pb-16 overflow-hidden">
                    <div class="max-w-[1400px] mx-auto px-6 relative z-10 flex flex-col md:flex-row items-center justify-between gap-8 md:gap-16">
                        <div class="flex-1">
                            <nav class="flex items-center gap-2 text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">
                                <Link href="/">Home</Link>
                                <i class="fas fa-chevron-right text-xs"></i>
                                <span>Catalog</span>
                                <i class="fas fa-chevron-right text-xs"></i>
                                <span class="text-emerald-500">{{ category.name }}</span>
                            </nav>
                            <h2 class="text-5xl sm:text-7xl font-black text-emerald-800 tracking-tighter uppercase leading-none">
                                {{ category.name }}
                            </h2>
                            <p class="text-slate-500 font-medium text-lg mt-6 max-w-2xl leading-relaxed">
                                {{ category.description || "Browse our " + category.name + " collection." }}
                            </p>
                        </div>
                        <!-- Optional Category Header Image -->
                        <div v-if="category.image" class="w-full md:w-[350px] lg:w-[450px] shrink-0">
                            <div class="aspect-[16/9] md:aspect-[4/3] rounded-3xl overflow-hidden shadow-xl border border-gray-50">
                                <img :src="category.image" class="w-full h-full object-cover shadow-inner" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="max-w-[1400px] mx-auto px-6 py-8 md:py-16 flex flex-col lg:flex-row gap-12">
                    
                    <!-- Mobile Filter Toggle -->
                    <div class="lg:hidden flex items-center justify-between border-b border-gray-100 pb-4">
                        <span class="text-sm font-black text-slate-800">{{ products.data.length }} Items found</span>
                        <button @click="mobileFiltersOpen = true" class="flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-600 rounded-lg text-xs font-black uppercase tracking-widest active:scale-95 transition-transform">
                            <i class="fas fa-filter"></i> Filters
                        </button>
                    </div>

                    <!-- Filters Drawer (Mobile) & Pin (Desktop) -->
                    <aside :class="mobileFiltersOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" class="fixed inset-y-0 left-0 w-4/5 max-w-sm bg-white shadow-2xl z-[60] lg:relative lg:shadow-none lg:z-auto lg:w-64 shrink-0 transition-transform duration-300 overflow-y-auto lg:overflow-visible">
                        <div class="p-6 lg:p-0">
                            <!-- Mobile Header -->
                            <div class="flex items-center justify-between lg:hidden mb-8 border-b border-gray-100 pb-4">
                                <h3 class="text-lg font-black uppercase tracking-widest text-emerald-800">Refine Selection</h3>
                                <button @click="mobileFiltersOpen = false" class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-slate-400 hover:text-rose-500"><i class="fas fa-times"></i></button>
                            </div>
                            
                            <div class="sticky top-32 space-y-10">
                                <div>
                                    <h3 class="hidden lg:block text-xs font-black uppercase tracking-widest text-slate-900 mb-6 pb-2 border-b border-gray-100">Refine Selection</h3>
                                    <div class="space-y-6">
                                        <div v-for="parentCat in (ecom?.categories || []).filter(c => !c.parent_id)" :key="parentCat.id" class="space-y-3">
                                            <Link @click="mobileFiltersOpen = false" :href="'/' + parentCat.slug" class="text-sm font-black uppercase tracking-widest block" :class="parentCat.id === category.id || parentCat.id === category.parent_id ? 'text-emerald-600' : 'text-slate-800 hover:text-emerald-500'">
                                                {{ parentCat.name }}
                                            </Link>
                                            <ul v-if="(ecom?.categories || []).filter(sub => sub.parent_id === parentCat.id).length" class="space-y-2 pl-3 border-l-2 border-emerald-50">
                                                <li v-for="subCat in (ecom?.categories || []).filter(sub => sub.parent_id === parentCat.id)" :key="subCat.id">
                                                    <Link @click="mobileFiltersOpen = false" :href="'/' + subCat.slug" class="text-xs font-bold transition-all block py-1" :class="subCat.id === category.id ? 'text-emerald-500' : 'text-slate-500 hover:text-emerald-500'">
                                                        {{ subCat.name }}
                                                    </Link>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </aside>

                    <!-- Mobile Overlay -->
                    <div v-if="mobileFiltersOpen" @click="mobileFiltersOpen = false" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[55] lg:hidden"></div>


                    <!-- Main Content Panel -->
                    <div class="flex-1">
                        <!-- Subcategory Quick Links (Visible if parent category) -->
                        <div v-if="(ecom?.categories || []).filter(sub => sub.parent_id === category.id).length" class="mb-12">
                            <h3 class="text-xl md:text-2xl font-black text-emerald-800 tracking-tighter uppercase mb-6 drop-shadow-sm">Shop by Category</h3>
                            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
                                <Link v-for="sub in (ecom?.categories || []).filter(sub => sub.parent_id === category.id)" :key="sub.id" :href="'/' + sub.slug" class="group relative aspect-[4/5] rounded-3xl overflow-hidden shadow-sm border border-emerald-50 bg-emerald-50/50 block">
                                    <div v-if="sub.image" class="absolute inset-0">
                                        <img :src="sub.image" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 blur-[2px] opacity-70 group-hover:blur-0 group-hover:opacity-100" />
                                    </div>
                                    <div v-else class="absolute inset-0 flex items-center justify-center text-4xl text-emerald-200 group-hover:scale-110 transition-transform duration-700">
                                        <i class="fas fa-tag"></i>
                                    </div>
                                    <div class="absolute inset-0 lg:bg-gradient-to-t bg-gradient-to-t from-emerald-900/90 via-emerald-800/40 to-transparent group-hover:from-emerald-900 transition-colors duration-500"></div>
                                    <div class="absolute inset-x-0 bottom-0 p-4 md:p-6 translate-y-2 group-hover:translate-y-0 transition-transform duration-300">
                                        <h4 class="text-white font-black text-sm md:text-base uppercase tracking-widest leading-tight shadow-black drop-shadow-lg">{{ sub.name }}</h4>
                                        <div class="w-8 h-1 bg-emerald-400 mt-3 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    </div>
                                </Link>
                            </div>
                        </div>

                        <!-- Product Grid -->
                        <div v-if="products.data.length" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-x-4 md:gap-x-8 gap-y-6 md:gap-y-10">
                            <div v-for="prod in products.data" :key="prod.id" class="group">
                                <div class="aspect-[3/4] rounded-3xl overflow-hidden bg-gray-50 border border-gray-100 relative mb-5 group-hover:shadow-2xl transition-all duration-500">
                                    <img :src="parsedFirstImage(prod.images)" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" />
                                    <!-- Use goToProduct inside category for faster direct checkout since sizes might exist -->
                                    <button @click="goToProduct(prod)" class="absolute bottom-4 left-4 right-4 h-12 bg-white/95 backdrop-blur-sm text-emerald-800 text-sm font-black uppercase tracking-widest rounded-2xl shadow-xl shadow-black/10 opacity-0 group-hover:opacity-100 translate-y-4 group-hover:translate-y-0 transition-all duration-300 hover:bg-emerald-500 hover:text-white">
                                        View Details
                                    </button>
                                </div>
                                <Link :href="'/' + prod.slug" class="block">
                                    <h4 class="text-sm font-bold text-slate-800 tracking-tight mb-1 truncate">{{ prod.name }}</h4>
                                    <p class="text-base md:text-lg font-black text-emerald-500">{{ currency }}{{ formatPrice(prod.price) }}</p>
                                </Link>
                            </div>
                        </div>
                        <div v-else class="py-32 text-center">
                            <div class="w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-6 text-emerald-200 text-3xl">
                                <i class="fas fa-search"></i>
                            </div>
                            <h3 class="text-xl font-black text-slate-900">No products found</h3>
                            <p class="text-slate-400 font-bold text-sm mt-2 uppercase tracking-widest">Checking our catalog for more updates...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- C.1 Search View -->
            <div v-else-if="view_type === 'search'" class="pb-32 bg-gray-50/50">
                <div class="bg-white border-b border-gray-100 pt-10 pb-8">
                    <div class="max-w-[1400px] mx-auto px-6">
                        <nav class="flex items-center gap-2 text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">
                            <Link href="/">Home</Link>
                            <i class="fas fa-chevron-right text-xs"></i>
                            <span>Search</span>
                            <i class="fas fa-chevron-right text-xs"></i>
                            <span class="text-emerald-500">"{{ search_query }}"</span>
                        </nav>
                        <h2 class="text-3xl md:text-5xl font-black text-emerald-800 tracking-tighter uppercase leading-none">
                            Results for "{{ search_query }}"
                        </h2>
                    </div>
                </div>
                
                <div class="max-w-[1400px] mx-auto px-6 py-12">
                    <div v-if="products?.data?.length" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-x-4 md:gap-x-6 gap-y-8 md:gap-y-12">
                        <div v-for="prod in products.data" :key="prod.id" class="group">
                            <div class="aspect-[3/4] rounded-3xl overflow-hidden bg-white border border-gray-100 relative mb-5 shadow-sm group-hover:shadow-2xl transition-all duration-500">
                                <img :src="parsedFirstImage(prod.images)" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" />
                                <button @click="goToProduct(prod)" class="absolute bottom-4 left-4 right-4 h-12 bg-emerald-800/95 backdrop-blur-sm text-white text-sm font-black uppercase tracking-widest rounded-2xl shadow-xl shadow-black/10 opacity-0 group-hover:opacity-100 translate-y-4 group-hover:translate-y-0 transition-all duration-300 hover:bg-emerald-500">
                                    View Options
                                </button>
                            </div>
                            <Link :href="'/' + prod.slug" class="block px-1">
                                <h4 class="text-sm font-bold text-slate-800 tracking-tight mb-1 truncate">{{ prod.name }}</h4>
                                <p class="text-base font-black text-emerald-500">{{ currency }}{{ formatPrice(prod.price) }}</p>
                            </Link>
                        </div>
                    </div>
                    <div v-else class="py-32 text-center bg-white rounded-3xl border border-gray-100 shadow-sm mt-8">
                        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300 text-3xl">
                            <i class="fas fa-search-minus"></i>
                        </div>
                        <h3 class="text-xl font-black text-slate-900">We couldn't find matches</h3>
                        <p class="text-slate-400 font-bold text-sm mt-2 uppercase tracking-widest max-w-sm mx-auto">Try adjusting your keywords or browsing our categories instead.</p>
                        <Link href="/" class="mt-8 inline-block px-8 py-3 bg-emerald-50 text-emerald-600 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-emerald-100 transition-colors">
                            Return Home
                        </Link>
                    </div>
                </div>
            </div>

            <!-- C. Product Detail View -->
            <div v-else-if="view_type === 'product' && product" class="pb-32 bg-white">
                <div class="max-w-[1400px] mx-auto px-6 py-10 md:py-20 flex flex-col lg:flex-row gap-8 lg:gap-20">
                    <!-- Gallery -->
                    <div class="w-full lg:w-[540px] shrink-0">
                        <div class="sticky top-32">
                             <div class="aspect-[3/4] rounded-[2.5rem] overflow-hidden bg-gray-50 border border-gray-100 shadow-xl">
                                 <img :src="activeImg || parsedFirstImage(product.images)" class="w-full h-full object-cover" />
                             </div>
                             <!-- Thumbnails (Simple) -->
                             <div class="flex gap-4 mt-6 overflow-x-auto no-scrollbar pb-2">
                                <div v-for="(img, i) in parsedImages(product.images)" :key="i"
                                    @click="activeImg = (typeof img === 'string' ? img : img.url)" 
                                    class="w-20 h-24 rounded-2xl bg-gray-100 cursor-pointer border-2 transition-all p-0.5"
                                    :class="(activeImg === (typeof img === 'string' ? img : img.url) || (!activeImg && i===0)) ? 'border-emerald-500 shadow-lg' : 'border-transparent'">
                                    <img :src="typeof img === 'string' ? img : img.url" class="w-full h-full object-cover rounded-xl" />
                                </div>
                             </div>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="flex-1 py-4">
                        <div class="inline-flex items-center gap-2 text-sm font-black uppercase tracking-[0.2em] text-emerald-500 mb-6">
                            {{ product.brand || 'Elite Selection' }} <span class="w-1.5 h-1.5 rounded-full bg-emerald-100"></span> In Stock
                        </div>
                        <h1 class="text-4xl sm:text-6xl font-black text-emerald-800 tracking-tighter leading-none mb-6">
                            {{ product.name }}
                        </h1>
                        <p class="text-lg text-slate-500 font-medium leading-relaxed mb-10 max-w-xl">
                            {{ product.short_description || "A masterfully crafted piece for those who settle for nothing but excellence. Timeless design meets modern functionality." }}
                        </p>
                        
                        <div class="flex items-baseline gap-4 mb-12 py-8 border-y border-slate-50">
                            <span class="text-5xl font-black text-emerald-500 tracking-tighter">{{ currency }}{{ formatPrice(product.price) }}</span>
                            <span v-if="product.mrp > product.price" class="text-xl text-slate-300 line-through font-bold italic">{{ currency }}{{ formatPrice(product.mrp) }}</span>
                        </div>

                        <!-- Product Variations (Size & Color) -->
                        <div v-if="product.size_chart || product.colour_swatches" class="space-y-8 mb-10 bg-gray-50 p-6 rounded-2xl border border-gray-100">
                            <div v-if="parsedSizes && parsedSizes.length">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-sm font-black uppercase text-slate-800 tracking-widest">Select Size</h4>
                                    <span class="text-sm font-bold uppercase text-emerald-500 tracking-widest cursor-pointer hover:underline">Size Guide <i class="fas fa-ruler-horizontal ml-1"></i></span>
                                </div>
                                <div class="flex flex-wrap gap-2 md:gap-3 cursor-pointer">
                                    <div v-for="sz in parsedSizes" :key="sz" @click="selectedSize = sz" :class="selectedSize === sz ? 'bg-emerald-800 text-white border-emerald-800 shadow-md scale-105' : 'bg-white text-slate-600 border-gray-200 hover:border-emerald-500'" class="min-w-[44px] h-11 px-3 flex items-center justify-center rounded-xl border font-black text-xs uppercase transition-all shadow-sm">
                                        {{ sz }}
                                    </div>
                                </div>
                                <p v-if="sizeError" class="text-red-500 text-xs font-bold uppercase mt-2 animate-pulse"><i class="fas fa-exclamation-circle text-sm mr-1"></i> Please select a size</p>
                            </div>

                            <div v-if="parsedColors && parsedColors.length">
                                <h4 class="text-sm font-black uppercase text-slate-800 tracking-widest mb-3">Select Color</h4>
                                <div class="flex flex-wrap gap-3 cursor-pointer">
                                    <div v-for="(col, i) in parsedColors" :key="i" @click="selectedColor = (col.name || col)" :class="selectedColor === (col.name || col) ? 'ring-2 ring-offset-2 ring-emerald-500 scale-110 shadow-md' : 'ring-1 ring-gray-200 hover:scale-105'" class="w-10 h-10 rounded-full shadow-inner transition-all relative group flex items-center justify-center" :style="{ backgroundColor: (col.hex || col) }">
                                        <div v-if="selectedColor === (col.name || col)" class="w-3 h-3 bg-white/30 backdrop-blur-sm rounded-full"></div>
                                        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 bg-emerald-900 text-white text-sm font-black uppercase tracking-widest px-3 py-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-all whitespace-nowrap z-10 pointer-events-none drop-shadow-xl after:content-[''] after:absolute after:top-full after:left-1/2 after:-translate-x-1/2 after:border-4 after:border-transparent after:border-t-emerald-900">{{ col.name || col }}</span>
                                    </div>
                                </div>
                                <p v-if="colorError" class="text-red-500 text-xs font-bold uppercase mt-2 animate-pulse"><i class="fas fa-exclamation-circle text-sm mr-1"></i> Please select a color</p>
                            </div>
                        </div>

                        <!-- Add to Cart Shell -->
                        <div class="space-y-8">
                            <div class="flex flex-col sm:flex-row gap-4">
                                <button @click="addConfiguredToCart(product)" class="flex-1 h-16 bg-emerald-800 text-white rounded-2xl font-black text-base md:text-xs uppercase tracking-[0.1em] shadow-xl hover:bg-emerald-600 transition-all active:scale-95 flex items-center justify-center gap-3 relative overflow-hidden group">
                                    <div class="absolute inset-x-0 bottom-0 h-1 bg-emerald-500 transform translate-y-1 group-hover:translate-y-0 transition-transform"></div>
                                    <i class="fas fa-shopping-bag text-sm md:text-base"></i> Add to Cart
                                </button>
                                <button @click="toggleWishlist(product)" :class="isWishlisted(product) ? 'text-rose-500 border-rose-100 bg-rose-50 shadow-inner' : 'text-slate-400 bg-white border-gray-200 hover:text-rose-500 hover:border-rose-200 hover:bg-rose-50/50 hover:shadow-md'" class="w-16 h-16 shrink-0 rounded-2xl border flex items-center justify-center transition-all group">
                                    <i :class="isWishlisted(product) ? 'fas fa-heart' : 'far fa-heart'" class="text-xl group-hover:scale-110 transition-transform"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Tabs/Description -->
                        <div class="mt-16 space-y-12">
                             <div>
                                 <h3 class="text-xs font-black uppercase tracking-widest text-slate-900 mb-4 pb-2 border-b border-emerald-50">Description</h3>
                                 <div class="text-slate-500 text-sm leading-relaxed font-medium space-y-4" v-html="product.description"></div>
                             </div>
                             <div class="grid grid-cols-2 gap-8 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">
                                 <div class="flex items-center gap-3"><i class="fas fa-shipping-fast text-emerald-500"></i> Local Shipping</div>
                                 <div class="flex items-center gap-3"><i class="fas fa-redo text-emerald-500"></i> 14-Day Exchange</div>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- D. Account Hub Views -->
            <div v-else-if="view_type.startsWith('account_')" class="pb-32 bg-gray-50/50">
                <div class="bg-white border-b border-gray-100 pt-10 pb-8">
                    <div class="max-w-[1400px] mx-auto px-6">
                        <h2 class="text-4xl md:text-5xl font-black text-emerald-800 tracking-tighter uppercase leading-none">
                            {{ view_type === 'account_orders' ? 'My Orders' : view_type === 'account_wishlist' ? 'Wishlist' : 'Dashboard' }}
                        </h2>
                    </div>
                </div>

                <div class="max-w-[1400px] mx-auto px-6 py-12 flex flex-col md:flex-row gap-8">
                    <!-- Sidebar menu -->
                    <aside class="w-full md:w-64 shrink-0">
                        <nav class="space-y-2">
                            <Link href="/customer/dashboard" :class="view_type === 'account_dashboard' ? 'bg-emerald-50 text-emerald-600' : 'hover:bg-gray-50 text-slate-600'" class="block px-4 py-3 rounded-lg font-bold text-sm uppercase tracking-widest transition-colors">Account Details</Link>
                            <Link href="/customer/orders" :class="view_type === 'account_orders' ? 'bg-emerald-50 text-emerald-600' : 'hover:bg-gray-50 text-slate-600'" class="block px-4 py-3 rounded-lg font-bold text-sm uppercase tracking-widest transition-colors">My Orders</Link>
                            <Link href="/customer/wishlist" :class="view_type === 'account_wishlist' ? 'bg-emerald-50 text-emerald-600' : 'hover:bg-gray-50 text-slate-600'" class="block px-4 py-3 rounded-lg font-bold text-sm uppercase tracking-widest transition-colors">Wishlist</Link>
                        </nav>
                    </aside>

                    <!-- Content -->
                    <div class="flex-1 bg-white p-8 rounded-2xl border border-gray-100 shadow-sm">
                        <template v-if="view_type === 'account_dashboard'">
                            <h3 class="text-xl font-black text-slate-900 mb-6">Account Overview</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="p-6 bg-gray-50 rounded-xl border border-gray-100">
                                    <p class="text-xs font-black uppercase text-slate-400 mb-1">Full Name</p>
                                    <p class="font-bold text-slate-900">{{ site.user?.name }}</p>
                                </div>
                                <div class="p-6 bg-gray-50 rounded-xl border border-gray-100">
                                    <p class="text-xs font-black uppercase text-slate-400 mb-1">Email Address</p>
                                    <p class="font-bold text-slate-900">{{ site.user?.email }}</p>
                                </div>
                            </div>
                        </template>

                        <template v-if="view_type === 'account_orders'">
                             <div v-if="orderList?.length" class="space-y-4">
                                  <div v-for="order in orderList" :key="order.id" class="p-6 border border-gray-100 rounded-xl flex items-center justify-between">
                                      <div>
                                          <p class="font-black text-slate-900">#{{ order.id }}</p>
                                          <p class="text-xs text-slate-500 font-bold uppercase mt-1">{{ new Date(order.created_at).toLocaleDateString() }}</p>
                                      </div>
                                      <div class="text-right">
                                          <p class="font-black text-emerald-600">{{ currency }}{{ formatPrice(order.total_amount) }}</p>
                                          <span class="px-3 py-1 bg-emerald-50 text-emerald-600 text-sm font-black uppercase rounded-full mt-2 inline-block shadow-sm">{{ order.status }}</span>
                                      </div>
                                  </div>
                             </div>
                             <div v-else class="text-center py-20 opacity-50">
                                  <i class="fas fa-box-open text-4xl mb-4"></i>
                                  <p class="font-bold uppercase tracking-widest text-sm">No orders found</p>
                             </div>
                        </template>

                        <template v-if="view_type === 'account_wishlist'">
                             <div v-if="wishlistProducts?.length" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6">
                                <div v-for="prod in wishlistProducts" :key="prod.id" class="group">
                                    <div class="aspect-[3/4] rounded-2xl overflow-hidden bg-gray-50 relative mb-3">
                                        <img :src="parsedFirstImage(prod.images)" class="w-full h-full object-cover" />
                                        <button @click="toggleWishlist(prod)" class="absolute top-3 right-3 w-8 h-8 bg-white rounded-full flex items-center justify-center text-rose-500 shadow-md transform active:scale-90 transition-all">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                        <button @click="addToCart(prod)" class="absolute bottom-3 left-3 right-3 py-2 bg-emerald-600 text-white rounded-lg opacity-0 group-hover:opacity-100 translate-y-2 group-hover:translate-y-0 transition-all shadow-md text-sm font-black uppercase">Add To Bag</button>
                                    </div>
                                    <Link :href="'/' + prod.slug" class="block">
                                        <h4 class="text-xs font-bold text-slate-800 truncate">{{ prod.name }}</h4>
                                        <p class="text-sm font-black text-emerald-600">{{ currency }}{{ formatPrice(prod.price) }}</p>
                                    </Link>
                                </div>
                             </div>
                             <div v-else class="text-center py-20 opacity-50">
                                  <i class="far fa-heart text-4xl mb-4"></i>
                                  <p class="font-bold uppercase tracking-widest text-sm">Wishlist is empty</p>
                             </div>
                        </template>
                    </div>
                </div>
            </div>
        </main>

        <!-- ══ Premium Site Footer ══ -->
        <SiteFooter :siteName="site.name" :categories="ecom?.categories || []" />

        <!-- ══ Premium Cart UI ══ -->
        <template v-if="site.type === 'ecommerce'">
            <transition enter-active-class="transition-all duration-500 ease-in-out" enter-from-class="translate-x-full" leave-active-class="transition-all duration-400 ease-in-out" leave-to-class="translate-x-full">
                <div v-if="cartOpen" class="fixed inset-y-0 right-0 w-full max-w-sm bg-white shadow-2xl z-[60] flex flex-col rounded-l-3xl overflow-hidden">
                    <div class="p-8 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-xl font-black text-emerald-800 uppercase tracking-tighter italic">Shopping Bag</h2>
                        <button @click="cartOpen = false" class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-slate-400 hover:bg-red-50 hover:text-red-500 transition-all"><i class="fas fa-times"></i></button>
                    </div>

                    <div class="flex-1 overflow-y-auto px-8 py-6 space-y-8 no-scrollbar">
                        <div v-for="item in cartItems" :key="item.variantId || item.product_id" class="flex gap-4 group">
                            <div class="w-20 h-24 rounded-2xl bg-gray-50 overflow-hidden shrink-0 border border-gray-100 group-hover:shadow-md transition-shadow">
                                <img v-if="item.image" :src="item.image" class="w-full h-full object-cover" />
                            </div>
                            <div class="flex-1 flex flex-col justify-between py-1 min-w-0">
                                <div>
                                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-tight truncate leading-none">{{ item.name }}</h4>
                                    <p class="text-sm font-black text-emerald-500 mt-2">{{ currency }}{{ formatPrice(item.unit_price) }}</p>
                                </div>
                                <div class="flex items-center justify-between mt-2">
                                    <div class="flex items-center gap-3 bg-gray-50 px-2.5 py-1 rounded-xl">
                                        <button @click="updateQty(item, -1)" class="text-slate-400 font-black hover:text-slate-900">－</button>
                                        <span class="text-xs font-black">{{ item.qty }}</span>
                                        <button @click="updateQty(item, 1)" class="text-slate-400 font-black hover:text-slate-900">＋</button>
                                    </div>
                                    <button @click="removeFromCart(item.variantId || item.product_id)" class="text-slate-300 hover:text-red-500 transition-colors"><i class="fas fa-trash-alt text-sm"></i></button>
                                </div>
                            </div>
                        </div>
                        <div v-if="!cartItems.length" class="text-center py-32 space-y-4">
                            <div class="text-slate-100 text-6xl"><i class="fas fa-shopping-bag"></i></div>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Your bag is empty</p>
                        </div>
                    </div>

                    <div class="p-8 bg-gray-50 border-t border-gray-100 space-y-6">
                        <div class="flex justify-between items-center px-2">
                            <span class="text-xs font-black uppercase text-slate-500 tracking-widest">Total Payable</span>
                            <span class="text-2xl font-black text-emerald-500">{{ currency }}{{ formatPrice(subtotal) }}</span>
                        </div>
                        <button @click="proceedToCheckout" :disabled="!cartItems.length || checkingOut"
                            class="w-full py-5 bg-emerald-500 text-white rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl shadow-emerald-500/20 hover:bg-emerald-700 transition-all flex items-center justify-center gap-2">
                             <i v-if="checkingOut" class="fas fa-spinner fa-spin"></i>
                             Checkout Now
                        </button>
                        <p class="text-center text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Secure Checkout Powered by Razorpay</p>
                    </div>
                </div>
            </transition>
            <div v-if="cartOpen" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[55]" @click="cartOpen = false"></div>
            
            <!-- Checkout Modal -->
            <transition enter-active-class="animate-fade-in-down" leave-to-class="opacity-0 scale-95 transition-all">
                <div v-if="checkoutOpen" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[70] flex items-center justify-center p-4">
                    <div class="bg-white rounded-3xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl relative">
                        <button @click="checkoutOpen = false" class="absolute top-6 right-6 w-10 h-10 bg-gray-50 rounded-full flex items-center justify-center text-slate-400 hover:text-red-500 transition-colors z-10"><i class="fas fa-times"></i></button>
                        <div class="p-8 md:p-12">
                            <h3 class="text-3xl font-black text-emerald-800 uppercase tracking-tighter italic mb-8">Secure Checkout</h3>
                            
                            <form @submit.prevent="processCheckout" class="space-y-8">
                                <!-- Contact Info -->
                                <div class="space-y-4">
                                    <h4 class="text-xs font-black uppercase text-slate-400 tracking-widest border-b pb-2">Contact Information</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-bold text-slate-500 uppercase mb-1">Email <span class="text-red-500">*</span></label>
                                            <input v-model="checkoutForm.guest_email" type="email" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-slate-500 uppercase mb-1">Phone <span class="text-red-500">*</span></label>
                                            <input v-model="checkoutForm.guest_phone" type="tel" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500" />
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Shipping Address -->
                                <div class="space-y-4">
                                    <h4 class="text-xs font-black uppercase text-slate-400 tracking-widest border-b pb-2">Shipping Address</h4>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-bold text-slate-500 uppercase mb-1">Full Name <span class="text-red-500">*</span></label>
                                            <input v-model="checkoutForm.shipping.name" type="text" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-slate-500 uppercase mb-1">Street Address <span class="text-red-500">*</span></label>
                                            <input v-model="checkoutForm.shipping.line1" type="text" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500" />
                                        </div>
                                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                            <div class="col-span-2 md:col-span-1">
                                                <label class="block text-sm font-bold text-slate-500 uppercase mb-1">City <span class="text-red-500">*</span></label>
                                                <input v-model="checkoutForm.shipping.city" type="text" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-bold text-slate-500 uppercase mb-1">State <span class="text-red-500">*</span></label>
                                                <input v-model="checkoutForm.shipping.state" type="text" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-bold text-slate-500 uppercase mb-1">PIN Code <span class="text-red-500">*</span></label>
                                                <input v-model="checkoutForm.shipping.zip" type="text" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-8 pt-6 border-t border-gray-100 flex items-center justify-between">
                                    <div class="leading-tight">
                                        <p class="text-sm font-bold uppercase text-slate-400 tracking-widest">Calculated Subtotal</p>
                                        <p class="text-2xl font-black text-emerald-600 tracking-tighter">{{ currency }}{{ formatPrice(subtotal) }}</p>
                                    </div>
                                    <button type="submit" :disabled="checkingOut" class="px-8 py-4 bg-emerald-500 text-white rounded-xl font-black uppercase tracking-widest text-sm hover:bg-emerald-600 shadow-xl shadow-emerald-500/20 transition-all flex items-center gap-3">
                                        <i v-if="checkingOut" class="fas fa-spinner fa-spin"></i>
                                        <span v-else>Complete via Razorpay <i class="fas fa-chevron-right ml-1"></i></span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </transition>
        </template>

        <!-- Toast -->
        <transition enter-active-class="translate-y-0" enter-from-class="translate-y-20" leave-to-class="translate-y-20">
            <div v-if="formMessage" class="fixed bottom-10 left-1/2 -translate-x-1/2 z-[100] px-6 py-3 bg-emerald-800 text-white rounded-2xl shadow-2xl font-black text-sm uppercase tracking-widest flex items-center gap-3">
                <i class="fas fa-check-circle text-emerald-400"></i> {{ formMessage }}
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';

// Block imports (standard from CMS palette)
import HeroBlock from '@/Pages/CMS/Components/CanvasBlocks/HeroBlock.vue';
import FeatureGrid from '@/Pages/CMS/Components/CanvasBlocks/FeatureGrid.vue';
import TextBlock from '@/Pages/CMS/Components/CanvasBlocks/TextBlock.vue';
import TestimonialBlock from '@/Pages/CMS/Components/CanvasBlocks/TestimonialBlock.vue';
import PricingBlock from '@/Pages/CMS/Components/CanvasBlocks/PricingBlock.vue';
import GalleryBlock from '@/Pages/CMS/Components/CanvasBlocks/GalleryBlock.vue';
import StatsBlock from '@/Pages/CMS/Components/CanvasBlocks/StatsBlock.vue';
import CtaBlock from '@/Pages/CMS/Components/CanvasBlocks/CtaBlock.vue';
import FormBlock from '@/Pages/CMS/Components/CanvasBlocks/FormBlock.vue';
import ProductGrid from '@/Pages/CMS/Components/CanvasBlocks/ProductGrid.vue';
import FooterBlock from '@/Pages/CMS/Components/CanvasBlocks/FooterBlock.vue';
import NavbarBlock from '@/Pages/CMS/Components/CanvasBlocks/NavbarBlock.vue';
import TeamBlock from '@/Pages/CMS/Components/CanvasBlocks/TeamBlock.vue';
import FaqBlock from '@/Pages/CMS/Components/CanvasBlocks/FaqBlock.vue';
import VideoBlock from '@/Pages/CMS/Components/CanvasBlocks/VideoBlock.vue';
import BannerBlock from '@/Pages/CMS/Components/CanvasBlocks/BannerBlock.vue';
import UnknownBlock from '@/Pages/CMS/Components/CanvasBlocks/UnknownBlock.vue';
// Fashion store specific
import HeroVideo from '@/Pages/CMS/Public/Blocks/HeroVideo.vue';
import CategoryCards from '@/Pages/CMS/Public/Blocks/CategoryCards.vue';
import FlashSale from '@/Pages/CMS/Public/Blocks/FlashSale.vue';
import ProductCarousel from '@/Pages/CMS/Public/Blocks/ProductCarousel.vue';
import BrandLogos from '@/Pages/CMS/Public/Blocks/BrandLogos.vue';
import Testimonials from '@/Pages/CMS/Public/Blocks/Testimonials.vue';
import Newsletter from '@/Pages/CMS/Public/Blocks/Newsletter.vue';
import SiteFooter from '@/Pages/CMS/Public/SiteFooter.vue';

const BLOCK_MAP = {
    hero: HeroBlock, features: FeatureGrid, text: TextBlock, testimonial: TestimonialBlock,
    pricing: PricingBlock, gallery: GalleryBlock, stats: StatsBlock, cta: CtaBlock,
    form: FormBlock, products: ProductGrid, footer: FooterBlock, navbar: NavbarBlock,
    team: TeamBlock, faq: FaqBlock, video: VideoBlock, banner: BannerBlock,
    hero_video: HeroVideo, category_cards: CategoryCards, flash_sale: FlashSale,
    product_carousel: ProductCarousel, brand_logos: BrandLogos, testimonials: Testimonials, newsletter: Newsletter
};
const getBlockComponent = (type) => BLOCK_MAP[type] || UnknownBlock;

const props = defineProps({
    view_type: { type: String, default: 'page' },
    site:      { type: Object, required: true },
    page:      { type: Object, default: () => ({}) },
    blocks:    { type: Array,  default: () => [] },
    seo:       { type: Object, default: () => ({}) },
    nav_pages: { type: Array,  default: () => [] },
    theme:     { type: Object, default: () => ({}) },
    ecom:      { type: Object, default: null },
    pwa:       { type: Object, default: null },
    forms:     { type: Array,  default: () => [] },
    category:  { type: Object, default: null },
    products:  { type: Object, default: () => ({ data: [] }) },
    product:   { type: Object, default: null },
    related:   { type: Array,  default: () => [] },
    orderList: { type: Array,  default: () => [] },
    wishlistProducts: { type: Array, default: () => [] },
    wishlist_ids: { type: Array, default: () => [] },
    search_query: { type: String, default: '' },
});

// State
const activeImg = ref(null);
const cartOpen = ref(false);
const checkoutOpen = ref(false);
const mobileFiltersOpen = ref(false);
const cartItems = ref([]);
const checkingOut = ref(false);
const formMessage = ref('');
const searchQuery = ref(props.search_query);

// Variant Pickers
const selectedSize = ref('');
const selectedColor = ref('');
const sizeError = ref(false);
const colorError = ref(false);
const checkoutForm = ref({
    guest_email: props.site.user?.email || '',
    guest_phone: '',
    shipping: { name: props.site.user?.name || '', line1: '', city: '', state: '', zip: '' }
});

// Computed
const currency = computed(() => props.ecom?.currency || props.site?.currency || '₹');
const formatPrice = (n) => Number(n || 0).toLocaleString('en-IN');
const subtotal = computed(() => cartItems.value.reduce((s, i) => s + (i.unit_price * i.qty), 0));
const cartCount = computed(() => cartItems.value.reduce((s, i) => s + i.qty, 0));

const themeVars = computed(() => {
    const colors = props.theme?.colors || {};
    const vars = {};
    Object.entries(colors).forEach(([k, v]) => { vars[`--color-${k}`] = v; });
    return vars;
});

const parsedSizes = computed(() => {
    if (!props.product?.size_chart) return [];
    try { return JSON.parse(props.product.size_chart); } catch { return []; }
});

const parsedColors = computed(() => {
    if (!props.product?.colour_swatches) return [];
    try { return JSON.parse(props.product.colour_swatches); } catch { return []; }
});

// Helpers
const parsedFirstImage = (imgs) => {
    try {
        const arr = (typeof imgs === 'string') ? JSON.parse(imgs) : imgs;
        return arr?.[0]?.url || arr?.[0] || 'https://images.unsplash.com/photo-1555529669-e69e7aa0ba9a?auto=format&fit=crop&q=80&w=400';
    } catch { return 'https://images.unsplash.com/photo-1555529669-e69e7aa0ba9a?auto=format&fit=crop&q=80&w=400'; }
};

const parsedImages = (imgs) => {
    try { return (typeof imgs === 'string') ? JSON.parse(imgs) : imgs || []; } 
    catch { return []; }
};

// Actions
onMounted(() => {
    const saved = sessionStorage.getItem(`cart_${props.site.id}`);
    if (saved) cartItems.value = JSON.parse(saved);
});

const saveCart = () => sessionStorage.setItem(`cart_${props.site.id}`, JSON.stringify(cartItems.value));

const performSearch = () => {
    if (searchQuery.value && searchQuery.value.trim()) {
        window.location.href = `/search?q=${encodeURIComponent(searchQuery.value.trim())}`;
    }
};

const addConfiguredToCart = (product) => {
    sizeError.value = false;
    colorError.value = false;
    
    // Validation
    if (parsedSizes.value.length && !selectedSize.value) { sizeError.value = true; return; }
    if (parsedColors.value.length && !selectedColor.value) { colorError.value = true; return; }
    
    // Add explicitly configured unit
    const variantId = `${product.id}-${selectedSize.value || 'none'}-${selectedColor.value || 'none'}`;
    const existing = cartItems.value.find(i => i.variantId === variantId);
    
    if (existing) { existing.qty++; } 
    else {
        let variantName = product.name;
        if (selectedSize.value || selectedColor.value) {
            variantName += ` (${[selectedSize.value, selectedColor.value].filter(Boolean).join(' • ')})`;
        }
        
        cartItems.value.push({
            product_id: product.id, 
            variantId: variantId,
            name: variantName, 
            unit_price: Number(product.price), 
            qty: 1,
            image: parsedFirstImage(product.images),
            size: selectedSize.value,
            color: selectedColor.value
        });
    }
    saveCart();
    cartOpen.value = true;
};

// Raw addToCart override for unconfigured Category list buttons
const addToCart = (product) => {
    // If it has variants, redirect to detail page first 
    if (product.size_chart?.length > 4 || product.colour_swatches?.length > 4) {
        return goToProduct(product);
    }
    const variantId = `${product.id}`;
    const existing = cartItems.value.find(i => i.variantId === variantId);
    if (existing) { existing.qty++; } else {
        cartItems.value.push({
            product_id: product.id, variantId: variantId,
            name: product.name, unit_price: Number(product.price), qty: 1,
            image: parsedFirstImage(product.images)
        });
    }
    saveCart();
    cartOpen.value = true;
};

const goToProduct = (p) => { 
    window.location.href = `/${p.slug}`; 
};

const getCategoryProducts = (catId) => {
    if (!props.ecom?.homepage_products) return [];
    return props.ecom.homepage_products.filter(p => p.category_id === parseInt(catId) || p.sub_category_id === parseInt(catId)).slice(0, 10);
};

const getCatSlug = (catId) => {
    const cat = props.ecom?.categories?.find(c => c.id === parseInt(catId));
    return cat ? cat.slug : '#';
};

const removeFromCart = (pid) => {
    const idx = cartItems.value.findIndex(i => (i.variantId === pid) || (i.product_id === pid));
    if (idx !== -1) cartItems.value.splice(idx, 1);
    saveCart();
};
const updateQty = (item, delta) => {
    item.qty = Math.max(1, item.qty + delta);
    saveCart();
};

const showToast = (m) => {
    formMessage.value = m;
    setTimeout(() => { formMessage.value = ''; }, 3000);
};

const submitForm = async ({ formId, data }) => {
    try { await axios.post(`/cms/forms/${formId}/submit`, data); showToast('Submission Recorded ✓'); } 
    catch { showToast('Submission Error'); }
};

const proceedToCheckout = () => {
    if (!cartItems.value.length) return;
    cartOpen.value = false;
    checkoutOpen.value = true;
};

const loadRazorpayScript = () => {
    return new Promise((resolve) => {
        if (window.Razorpay) return resolve(true);
        const script = document.createElement('script');
        script.src = 'https://checkout.razorpay.com/v1/checkout.js';
        script.onload = () => resolve(true);
        script.onerror = () => resolve(false);
        document.body.appendChild(script);
    });
};

const processCheckout = async () => {
    checkingOut.value = true;
    try {
        const payload = {
            site_id: props.site.id,
            items: cartItems.value,
            guest_email: checkoutForm.value.guest_email,
            guest_phone: checkoutForm.value.guest_phone,
            shipping_address: checkoutForm.value.shipping,
            billing_address: checkoutForm.value.shipping, // Using same for now
        };
        const { data } = await axios.post('/cms/checkout/create-order', payload);
        
        await loadRazorpayScript();
        
        const options = {
            key: data.razorpay_key_id,
            amount: data.amount,
            currency: data.currency,
            name: props.site.name,
            description: "Store Purchase",
            order_id: data.razorpay_order_id,
            handler: async function (response) {
                try {
                    await axios.post('/cms/checkout/verify-payment', {
                        cms_order_id: data.cms_order_id,
                        razorpay_order_id: response.razorpay_order_id,
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_signature: response.razorpay_signature
                    });
                    
                    // Clear cart & redirect to orders
                    cartItems.value = [];
                    saveCart();
                    checkoutOpen.value = false;
                    showToast('Payment Successful! Order Confirmed.');
                    setTimeout(() => {
                        window.location.href = props.site.user ? '/customer/orders' : '/';
                    }, 2000);
                } catch (err) {
                    showToast('Payment Verification Failed!');
                }
            },
            prefill: {
                name: checkoutForm.value.shipping.name,
                email: checkoutForm.value.guest_email,
                contact: checkoutForm.value.guest_phone
            },
            theme: { color: props.theme?.colors?.primary || '#10b981' }
        };
        
        const rzp = new window.Razorpay(options);
        rzp.on('payment.failed', function (res) {
            showToast('Payment Failed or Cancelled');
            checkingOut.value = false;
        });
        rzp.open();
    } catch (e) {
        showToast('Checkout Initiation Failed. Please check details.');
    } finally {
        checkingOut.value = false;
    }
};

const isWishlisted = (prod) => {
    return props.wishlist_ids?.includes(prod.id);
};

const toggleWishlist = (prod) => {
    router.post(`/customer/wishlist/${prod.id}`, {}, {
        preserveScroll: true,
        preserveState: false,
    });
};

</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

.site-root { font-family: 'Inter', sans-serif; }
.no-scrollbar::-webkit-scrollbar { display: none; }

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-15px); }
}
.animate-float { animation: float 6s ease-in-out infinite; }

@keyframes bounce-short {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-3px); }
}
.animate-bounce-short { animation: bounce-short 1s infinite; }

.animate-fade-in-down {
    animation: fadeInDown 0.8s ease-out;
}
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
