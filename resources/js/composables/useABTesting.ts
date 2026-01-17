import { ref, computed, onMounted, type Ref, type ComputedRef, watch } from 'vue';
import axios from 'axios';

/**
 * Experiment variant data.
 */
export interface ExperimentVariant {
  experiment_id: number;
  experiment_slug: string;
  variant_id: number;
  variant_slug: string;
  is_control: boolean;
  content: Record<string, any>;
}

/**
 * Conversion goals.
 */
export const ConversionGoals = {
  CLICK: 'click',
  SIGNUP: 'signup',
  PURCHASE: 'purchase',
  ENGAGEMENT: 'engagement',
  SCROLL: 'scroll',
  TIME_ON_PAGE: 'time_on_page',
  FORM_SUBMIT: 'form_submit',
  SHARE: 'share',
  DOWNLOAD: 'download',
  VIDEO_PLAY: 'video_play',
} as const;

export type ConversionGoal = typeof ConversionGoals[keyof typeof ConversionGoals];

// Cache for fetched variants
const variantCache = new Map<string, ExperimentVariant>();

// Pending promises for in-flight requests
const pendingRequests = new Map<string, Promise<ExperimentVariant | null>>();

/**
 * Fetch variant for a location (with caching and deduplication).
 */
async function fetchVariant(location: string): Promise<ExperimentVariant | null> {
  // Check cache first
  if (variantCache.has(location)) {
    return variantCache.get(location) || null;
  }

  // Check if request is already in flight
  if (pendingRequests.has(location)) {
    return pendingRequests.get(location)!;
  }

  // Make the request
  const promise = axios.get<{ data: ExperimentVariant | null }>(`/api/ab/variant/${location}`)
    .then(response => {
      const variant = response.data.data;
      if (variant) {
        variantCache.set(location, variant);
      }
      pendingRequests.delete(location);
      return variant;
    })
    .catch(error => {
      console.warn(`Failed to fetch A/B variant for location "${location}":`, error);
      pendingRequests.delete(location);
      return null;
    });

  pendingRequests.set(location, promise);
  return promise;
}

/**
 * Track a conversion for an experiment.
 */
async function trackConversionApi(experimentSlug: string, goal: string): Promise<boolean> {
  try {
    const response = await axios.post<{ data: { tracked: boolean } }>(
      `/api/ab/conversion/${experimentSlug}`,
      { goal }
    );
    return response.data.data.tracked;
  } catch (error) {
    console.warn(`Failed to track conversion for "${experimentSlug}":`, error);
    return false;
  }
}

/**
 * Track a conversion by location.
 */
async function trackConversionByLocationApi(location: string, goal?: string): Promise<boolean> {
  try {
    const response = await axios.post<{ data: { tracked: boolean } }>(
      `/api/ab/conversion-location/${location}`,
      { goal }
    );
    return response.data.data.tracked;
  } catch (error) {
    console.warn(`Failed to track conversion for location "${location}":`, error);
    return false;
  }
}

/**
 * Composable for a single A/B test location.
 *
 * @param location - The experiment location identifier
 */
export function useABTest(location: string) {
  const variant = ref<ExperimentVariant | null>(null);
  const loading = ref(true);
  const error = ref<string | null>(null);

  // Computed helpers
  const isControl = computed(() => variant.value?.is_control ?? true);
  const content = computed(() => variant.value?.content ?? {});
  const variantSlug = computed(() => variant.value?.variant_slug ?? 'control');

  /**
   * Load the variant for this location.
   */
  const load = async () => {
    loading.value = true;
    error.value = null;

    try {
      variant.value = await fetchVariant(location);
    } catch (e: any) {
      error.value = e.message || 'Failed to load experiment variant';
    } finally {
      loading.value = false;
    }
  };

  /**
   * Track a conversion for this experiment.
   */
  const trackConversion = async (goal: ConversionGoal | string = ConversionGoals.CLICK) => {
    if (!variant.value) {
      return trackConversionByLocationApi(location, goal);
    }
    return trackConversionApi(variant.value.experiment_slug, goal);
  };

  /**
   * Get a specific content value with fallback.
   */
  const getContent = <T>(key: string, fallback: T): T => {
    return (content.value[key] as T) ?? fallback;
  };

  // Load on mount
  onMounted(load);

  return {
    variant,
    loading,
    error,
    isControl,
    content,
    variantSlug,
    load,
    trackConversion,
    getContent,
  };
}

/**
 * Composable for multiple A/B test locations.
 *
 * @param locations - Array of experiment location identifiers
 */
export function useABTests(locations: string[]) {
  const variants = ref<Record<string, ExperimentVariant>>({});
  const loading = ref(true);
  const error = ref<string | null>(null);

  /**
   * Load variants for all locations.
   */
  const load = async () => {
    loading.value = true;
    error.value = null;

    try {
      const response = await axios.post<{ data: Record<string, ExperimentVariant> }>(
        '/api/ab/variants',
        { locations }
      );

      variants.value = response.data.data;

      // Update cache
      Object.entries(response.data.data).forEach(([loc, variant]) => {
        variantCache.set(loc, variant);
      });
    } catch (e: any) {
      error.value = e.message || 'Failed to load experiment variants';
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get variant for a specific location.
   */
  const getVariant = (location: string): ExperimentVariant | null => {
    return variants.value[location] || null;
  };

  /**
   * Check if a location has a non-control variant.
   */
  const hasVariant = (location: string): boolean => {
    const v = variants.value[location];
    return v !== undefined && !v.is_control;
  };

  /**
   * Track conversion for a specific location.
   */
  const trackConversion = async (location: string, goal: ConversionGoal | string = ConversionGoals.CLICK) => {
    const v = variants.value[location];
    if (v) {
      return trackConversionApi(v.experiment_slug, goal);
    }
    return trackConversionByLocationApi(location, goal);
  };

  // Load on mount
  onMounted(load);

  return {
    variants,
    loading,
    error,
    load,
    getVariant,
    hasVariant,
    trackConversion,
  };
}

/**
 * Reactive composable that returns content based on variant.
 *
 * @param location - The experiment location identifier
 * @param controlContent - Content to show for control group
 * @param variantContent - Content to show for variant group(s)
 */
export function useABContent<T>(
  location: string,
  controlContent: T,
  variantContent: T | Record<string, T>
): {
  content: ComputedRef<T>;
  loading: Ref<boolean>;
  isControl: ComputedRef<boolean>;
  trackConversion: (goal?: ConversionGoal | string) => Promise<boolean>;
} {
  const { variant, loading, isControl, trackConversion } = useABTest(location);

  const content = computed<T>(() => {
    if (!variant.value || variant.value.is_control) {
      return controlContent;
    }

    // If variantContent is a record keyed by variant slug
    if (typeof variantContent === 'object' && variantContent !== null && !Array.isArray(variantContent)) {
      const record = variantContent as Record<string, T>;
      const slug = variant.value.variant_slug;
      if (slug in record) {
        return record[slug];
      }
    }

    return variantContent as T;
  });

  return {
    content,
    loading,
    isControl,
    trackConversion,
  };
}

/**
 * Composable for tracking clicks on A/B test elements.
 *
 * @param location - The experiment location identifier
 */
export function useABClickTracking(location: string) {
  const { trackConversion } = useABTest(location);
  const conversionTracked = ref(false);

  /**
   * Click handler that tracks conversion.
   */
  const onClick = async (event?: MouseEvent) => {
    if (!conversionTracked.value) {
      conversionTracked.value = true;
      await trackConversion(ConversionGoals.CLICK);
    }
  };

  /**
   * Get click handler props for an element.
   */
  const clickProps = computed(() => ({
    onClick,
  }));

  return {
    onClick,
    clickProps,
    conversionTracked,
  };
}

/**
 * Clear the variant cache (useful for testing or when experiments change).
 */
export function clearABTestCache(): void {
  variantCache.clear();
}

/**
 * Preload variants for specified locations.
 * Useful for critical above-the-fold content.
 */
export async function preloadVariants(locations: string[]): Promise<void> {
  try {
    const response = await axios.post<{ data: Record<string, ExperimentVariant> }>(
      '/api/ab/variants',
      { locations }
    );

    Object.entries(response.data.data).forEach(([loc, variant]) => {
      variantCache.set(loc, variant);
    });
  } catch (error) {
    console.warn('Failed to preload A/B variants:', error);
  }
}

// Export constants
export { ConversionGoals as ABGoals };
