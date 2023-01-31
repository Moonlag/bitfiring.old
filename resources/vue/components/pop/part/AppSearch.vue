<template>
    <div id="main_search">
        <div class="search-wrap container">
            <div class="search-prov">
                <button class="search-prov__btn" aria-label="search submit">
                    <picture>
                        <source srcset="/assets/img/common/search.svg" type="image/webp">
                        <img src="/assets/img/common/search.svg" alt="search" loading="lazy">
                    </picture>
                </button>
                <input
                    class="search-prov__field"
                    type="text"
                    placeholder="Search"
                    name="search_111"
                    id="search_111"
                    aria-label="search">
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "AppSearch"
}
</script>

<style scoped>

</style>
