## RgTools

RgTools is a modern rewrite of RemoveGrain, Repair, BackwardClense, Clense, ForwardClense and VerticalCleaner in a single plugin. RgTools is mostly backward compatible to the original plugins.

Some routines might be slightly less efficient than original, some are faster. Output of a few RemoveGrain modes is not exactly identical to the original due to some minor rounding differences which you shouldn't care about. Other functions should be identical.

This plugin is written from scratch and licensed under the [MIT license][1]. Some modes of RemoveGrain and Repair were taken from the Firesledge's Dither package.

v0.96 (20170609)
- RemoveGrain: AVX2. Available when Avisynth+ reports AVX2 usability
  Can be disabled with new parameter: optAvx2=false
- Clense, ForwardClense, BackwardClense: ignore planar colorspace checking when planar=true. Like in RemoveGrain and Repair.
- Fix: Mode 11 and 13 for 32 bit float colorspaces (which worked like mode 10 and 12)

v0.95
- Fix: RemoveGrain Mode 20: overflow at 14 and 16 bit depths in SSE4 (stripes)
- Repair: error on unaligned frames (unaligned crop) instead of access violation error

v0.94
- Clense: new parameter (from v0.9): bool reduceflicker (default false)
- Clense: dummy compatibility parameters: bool planar, int cache
- autoregister filter MT modes as NICE_FILTER for Avisynth+
  except for Clense: when reduceflicker is true, MULTI_INSTANCE MT mode is reported
- alignment check in Repair and RemoveGrain (anti-unaligned crop measures)  

v0.93: new pixel formats are supported
- 10, 12, 14, 16 bit and float 
- Planar RGB, RGBA and YUVA (alpha plane is copied)


### Functions
```
RemoveGrain(clip c, int "mode", int "modeU", int "modeV", bool "planar")
```
Purely spatial denoising function, includes 24 different modes. Additional info can be found in the [wiki][2].

```
Repair(clip c, int "mode", int "modeU", int "modeV", bool "planar")
```
Repairs unwanted artifacts from (but not limited to) RemoveGrain, includes 24 modes.

```
Clense(clip c, clip "previous", clip "next", bool "grey", bool "reduceflicker", bool "planar", int "cache")
```
Temporal median of three frames. Identical to `MedianBlurTemporal(0,0,0,1)` but a lot faster. Can be used as a building block for [many][3] [fancy][4] [medians][5].
If reduceflicker is true, the (n-1)th source frame is reused from the previous "clensed" frame, that the filter stored internally. 
This works however only if Clense is getting frame requests sequentally.
Parameters "planar" and "cache" are dummy, they exist for compatibility reasons

```
ForwardClense(clip c, bool "grey", bool "planar", int "cache")
```
Modified version of Clense that works on current and next frames.
Parameters "planar" and "cache" are dummy, they exist for compatibility reasons

```
BackwardClense(clip c, bool "grey", bool "planar", int "cache")
```
Modified version of Clense that works on current and previous frames.
Parameters "planar" and "cache" are dummy, they exist for compatibility reasons

```
VerticalCleaner(clip c, int "mode", int "modeU", int "modeV", bool "planar")
```
Very fast vertical median filter. Has only two modes.


  [1]: http://opensource.org/licenses/MIT
  [2]: https://github.com/tp7/RgTools/wiki/RemoveGrain
  [3]: http://mechaweaponsvidya.wordpress.com/2014/01/31/enter-title-here/
  [4]: http://mechaweaponsvidya.wordpress.com/2014/04/23/ricing-your-temporal-medians-for-maximum-speed/
  [5]: http://mechaweaponsvidya.wordpress.com/2014/05/14/clense-versus-mt_clamp/
