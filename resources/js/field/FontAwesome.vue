<template>
    <div class="flex flex-row">

        <div v-if="value" style="min-width: 45px">
            <span class="mr-2">
                <span :key="renderKey">
                    <i :class="value.value + ' fa-2x'" />
                </span>
            </span>
        </div>

        <div class="flex-col w-full">
            <v-select
                ref="input"
                class="flex-1"
                :name="name"
                :clearable="config.clearable"
                :disabled="config.disabled || isReadOnly || (config.multiple && limitReached)"
                :placeholder="__('Search icons')"
                :searchable="config.searchable || config.taggable"
                :taggable="config.taggable"
                :push-tags="config.push_tags"
                :multiple="config.multiple"
                :close-on-select="true"
                :value="value"
                :options="options"
                :filtarable="false"
                :create-option="(value) => ({ value, label: value})"
                @input="vueSelectUpdated"
                @search="fetchOptions">

                <template #option="{ value, label, styles }">
                    <span class="flex items-center">
                        <i :class="getPreviewIconClass(value, styles) + ' fa-xl'" aria-hidden="true"></i>
                        <span class="ml-2" v-text="__(label)" />
                    </span>
                </template>

                <template #selected-option="{ value, label, styles }">
                    <span class="flex items-center">
                        <span v-text="__(label)" />
                    </span>
                </template>

                <template #search="{ events, attributes }" v-if="config.multiple">
                    <input
                        :placeholder="config.placeholder"
                        class="vs__search"
                        type="search"
                        v-on="events"
                        v-bind="attributes"
                    >
                </template>

                <template #no-options>
                    <div class="text-sm text-grey-70 text-left py-1 px-2" v-text="__('No options to choose from.')" />
                </template>

            </v-select>

            <v-select
                class="flex-1 mt-1"
                :disabled="config.disabled || isReadOnly || (config.multiple && limitReached)"
                :placeholder="__('Select style')"
                :searchable="false"
                :close-on-select="true"
                :value="selectedStyle"
                :options="value.styles"
                :filtarable="false"
                :create-option="(value) => ({ value, label: value})"
                @input="vueSelectStyleUpdated"
                v-if="value && value.styles !== undefined && value.styles.length > 1">

                <template #option="{ value, label }">
                    <span class="flex items-center">
                        <span v-text="label.charAt(0).toUpperCase() + label.slice(1)" />
                    </span>
                </template>

                <template #selected-option="{ value, label }">
                    <span class="flex items-center">
                        <span v-text="label.charAt(0).toUpperCase() + label.slice(1)" />
                    </span>
                </template>

                <template #no-options>
                    <div class="text-sm text-grey-70 text-left py-1 px-2" v-text="__('No options to choose from.')" />
                </template>
            </v-select>
        </div>
    </div>
</template>

<script>
import HasInputOptions from './../../../../../../vendor/statamic/cms/resources/js/components/fieldtypes/HasInputOptions.js'

export default {

    mixins: [Fieldtype, HasInputOptions],

    data() {
        return {
            options: [],
            selectedStyle: 'solid',
            renderKey: 0,
        }
    },

    watch: {
        // whenever question changes, this function will run
        value(newValue, oldValue) {
            this.renderKey++;
        }
    },

    mounted() {
        // fetch options of mounted value using search api
        let val = this.value.split(" ");

        this.selectedStyle = val[0].replace("fa-", "");

        this.$axios.get(cp_url(`font-awesome/search/${val[1].replace("fa-", "")}`)).then(response => {
            if (response.data[0] !== undefined ) {
                this.vueSelectUpdated(response.data[0]);
            }
        }).catch(error => {
            console.log(error);
        });
    },

    computed: {
        replicatorPreview() {
            return this.value.label;
        },

        resetOnOptionsChange() {
            // Reset logic should only happen when the config value is true.
            // Nothing should be reset when it's false or undefined.
            if (this.config.reset_on_options_change !== true) return false;

            // Reset the value if the value doesn't exist in the new set of options.
            return (options, old, val) => {
                let opts = options.map(o => o.value);
                return !val.some(v => opts.includes(v.value));
            };
        },

        limitReached() {
            if (! this.config.max_items) return false;

            return this.currentLength >= this.config.max_items;
        },

        limitExceeded() {
            if (! this.config.max_items) return false;

            return this.currentLength > this.config.max_items;
        },

        currentLength() {
            if (this.value) {
                return (typeof this.value == 'string') ? 1 : this.value.length;
            }

            return 0;
        },

        limitIndicatorColor() {
            if (this.limitExceeded) {
                return 'text-red';
            } else if (this.limitReached) {
                return 'text-green';
            }

            return 'text-grey';
        },
    },

    methods: {
        fetchOptions: _.debounce(function (search, loading) {
            if (search.length > 0) {
                loading(true);

                this.$axios.get(cp_url(`font-awesome/search/${search}`)).then(response => {
                    this.options = response.data;
                    loading(false);
                }).catch(error => {
                    this.options = [];
                    loading(false);
                });
            }
        }, 500),

        focus() {
            this.$refs.input.focus();
        },

        vueSelectUpdated(value) {
            if (value) {
                let style = this.selectedStyle;

                if (!value.styles.includes(this.selectedStyle)) {
                    style = value.styles[0];

                    this.selectedStyle = style;
                }

                this.update({value: 'fa-'+ style +' fa-' + (value.rawValue || value.value), label: value.label, styles: value.styles, rawValue: (value.rawValue || value.value)});
            } else {
                this.update(null);
            }
        },

        vueSelectStyleUpdated(value) {
            if (value) {
                this.selectedStyle = value
            }

            this.$nextTick(() => {
                this.vueSelectUpdated(this.value);
            })
        },

        getPreviewIconClass(value, styles = []) {
            let style = styles[0] !== undefined ? styles[0] : 'solid';

            return `fa-${style} fa-${value}`;
        }
    }
};
</script>
