# Refactoring TODO

- [ ] Introduce an interface/value object for responsive image settings such as `lqipQuality`, `addLqip`, `addAvif`, `avifQuality`, etc.
- [ ] Unify naming for quality options, especially `lqipQuality` vs. `qualityAvif` / `avifQuality`.
- [ ] Speed up image processing by avoiding calculations based on the original picture when a smaller processed image would be sufficient.
  - Note: consider image quality / Shannon information trade-offs before changing this behavior.
