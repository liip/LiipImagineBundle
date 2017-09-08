
# Changelog

All notable alterations to this project will be documented in this
[`CHANGELOG.md`](https://github.com/liip/LiipImagineBundle/blob/1.0/CHANGELOG.md) file and all important upgrade
requirements will be enumerated in the [`UPGRADE.md`](https://github.com/liip/LiipImagineBundle/blob/1.0/UPGRADE.md) file.
This project adheres to [semantic versioning](http://semver.org/spec/v2.0.0.html).


## [Unreleased](https://github.com/liip/LiipImagineBundle/tree/HEAD)

*Note: Recent developments can be tracked via the
[latest changelog](https://github.com/liip/LiipImagineBundle/compare/1.9.1...HEAD), the
[active milestone](https://github.com/liip/LiipImagineBundle/milestone/16), as well as all
[open milestones](https://github.com/liip/LiipImagineBundle/milestones).*


## [v1.9.1](https://github.com/liip/LiipImagineBundle/tree/1.9.1)

*Released on* 2017-09-08 *and assigned* [`1.9.1`](https://github.com/liip/LiipImagineBundle/releases/tag/1.9.1) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.9.0...1.9.1)\).*

- __\[Console\]__ __\[BC BREAK\]__ The resolve command's --as-script/-s option/shortcut renamed to --machine-readable/-m \(fixes [\#988](https://github.com/liip/LiipImagineBundle/pull/988)\), its output updated to aligned with the resolve command, and the "--machine-readable/-m" option added.  [\#991](https://github.com/liip/LiipImagineBundle/pull/991) *([robfrawley](https://github.com/robfrawley))*


## [v1.9.0](https://github.com/liip/LiipImagineBundle/tree/1.9.0)

*Released on* 2017-08-30 *and assigned* [`1.9.0`](https://github.com/liip/LiipImagineBundle/releases/tag/1.9.0) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.8.0...1.9.0)\).*

- __\[Tests\]__ Fix filesystem loader deprecation message in tests. [\#982](https://github.com/liip/LiipImagineBundle/pull/982) *([robfrawley](https://github.com/robfrawley))*
- __\[Filter\]__ Add "centerright" and "centerleft" positions to background filter. [\#974](https://github.com/liip/LiipImagineBundle/pull/974) *([cmodijk](https://github.com/cmodijk))*
- __\[Config\]__ Allow to configure the HTTP response code for redirects. [\#970](https://github.com/liip/LiipImagineBundle/pull/970) *([lstrojny](https://github.com/lstrojny))*
- __\[Console\]__ Added --force option, renamed --filters to --filter, and made resolve command output pretty. [\#967](https://github.com/liip/LiipImagineBundle/pull/967) *([robfrawley](https://github.com/robfrawley))*
- __\[CS\]__ Fix two docblock annotations. [\#965](https://github.com/liip/LiipImagineBundle/pull/965) *([imanalopher](https://github.com/imanalopher))*
- __\[Data Loader\]__ __\[Deprecation\]__ The FileSystemLoader no longer accepts an array of data root paths; instead pass a FileSystemLocator, which should instead be passed said paths. [\#963](https://github.com/liip/LiipImagineBundle/pull/963/) *([robfrawley](https://github.com/robfrawley), [rpkamp](https://github.com/rpkamp))*
- __\[Composer\]__ Allow [avalanche123/Imagine](https://github.com/avalanche123/Imagine) version 0.7.0. [\#958](https://github.com/liip/LiipImagineBundle/pull/958) *([robfrawley](https://github.com/robfrawley))*
- __\[Data Loader\]__ __\[Documentation\]__ Add chain loader documentation. [\#957](https://github.com/liip/LiipImagineBundle/pull/957) *([robfrawley](https://github.com/robfrawley))*
- __\[Data Loader\]__ Add chain loader implementation. [\#953](https://github.com/liip/LiipImagineBundle/pull/953) *([robfrawley](https://github.com/robfrawley))*
- __\[CS\]__ Fix templating extension method return type. [\#951](https://github.com/liip/LiipImagineBundle/pull/951) *([imanalopher](https://github.com/imanalopher))*
- __\[Dependency Injection\]__ Fix compiler pass log message typo. [\#947](https://github.com/liip/LiipImagineBundle/pull/947) *([you-ser](https://github.com/you-ser))*
- __\[Travis\]__ Default to trusty container image \(with precise image for php 5.3\). [\#945](https://github.com/liip/LiipImagineBundle/pull/945) *([robfrawley](https://github.com/robfrawley))*
- __\[Enqueue\]__ Use simplified transport configuration. [\#942](https://github.com/liip/LiipImagineBundle/pull/942) *([makasim](https://github.com/makasim))*
- __\[Filter\]__ Add resolution loader implementation. [\#941](https://github.com/liip/LiipImagineBundle/pull/941) *([robfrawley](https://github.com/robfrawley))*
- __\[Travis\]__ Remove Symfony 3.3 from allowed failures. [\#940](https://github.com/liip/LiipImagineBundle/pull/940) *([robfrawley](https://github.com/robfrawley))*
- __\[Utility\]__ Use simplified Symfony kernel version comparison operation. [\#939](https://github.com/liip/LiipImagineBundle/pull/939) *([robfrawley](https://github.com/robfrawley))*


## [v1.8.0](https://github.com/liip/LiipImagineBundle/tree/1.8.0)

*Released on* 2017-05-08 *and assigned* [`1.8.0`](https://github.com/liip/LiipImagineBundle/releases/tag/1.8.0) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.7.4...1.8.0)\).*

- __\[Minor\]__ __\[Bug\]__ Revert to php-cs-fixer 1.x and run fixer. [\#927](https://github.com/liip/LiipImagineBundle/pull/927) *([robfrawley](https://github.com/robfrawley))*
- __\[Routing\]__ Deprecate XML routing file in favor of YAML. [\#925](https://github.com/liip/LiipImagineBundle/pull/925) *([robfrawley](https://github.com/robfrawley))*
- __\[Filter\]__ Add flip filter implementation to core. [\#920](https://github.com/liip/LiipImagineBundle/pull/920) *([robfrawley](https://github.com/robfrawley))*
- __\[Queue\]__ Resolve image caches in background using message queue. [\#919](https://github.com/liip/LiipImagineBundle/pull/919) *([makasim](https://github.com/makasim))*


## [v1.7.4](https://github.com/liip/LiipImagineBundle/tree/1.7.4)

*Released on* 2017-03-01 *and assigned* [`1.7.4`](https://github.com/liip/LiipImagineBundle/releases/tag/1.7.4) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.7.3...1.7.4)\).*

- __\[Bug\]__ Revert adding leading slash to S3 class names. [\#893](https://github.com/liip/LiipImagineBundle/pull/893) *([cedricziel](https://github.com/cedricziel))*


## [v1.7.3](https://github.com/liip/LiipImagineBundle/tree/1.7.3)

*Released on* 2017-03-01 *and assigned* [`1.7.3`](https://github.com/liip/LiipImagineBundle/releases/tag/1.7.3) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.7.2...1.7.3)\).*

- __\[Tests\]__ Support PHPUnit 5.x \(and remove depredations\). [\#887](https://github.com/liip/LiipImagineBundle/pull/887) *([robfrawley](https://github.com/robfrawley))*
- __\[Tests\]__ Assert expected deprecation using symfony/phpunit-bridge. [\#886](https://github.com/liip/LiipImagineBundle/pull/886) *([robfrawley](https://github.com/robfrawley))*
- __\[Minor\]__ __\[Documentation\]__ Fix typo in general filters documentation. [\#888](https://github.com/liip/LiipImagineBundle/pull/888) *([svenluijten](https://github.com/svenluijten))*
- __\[Data Loader\]__ Add bundle resources to safe path when requested. [\#883](https://github.com/liip/LiipImagineBundle/pull/883) *([bobvandevijver](https://github.com/bobvandevijver), [robfrawley](https://github.com/robfrawley))*
- __\[Tests\]__ Enable mongo unit tests on PHP7 using "mongo" => "mongodb" extension adapter. [\#882](https://github.com/liip/LiipImagineBundle/pull/882) *([robfrawley](https://github.com/robfrawley))*
- __\[Data Loader\]__ __\[Data Locator\]__ FileSystemLocator service must not be shared. [\#875](https://github.com/liip/LiipImagineBundle/pull/875) *([robfrawley](https://github.com/liip/LiipImagineBundle/pull/875))*


## [v1.7.2](https://github.com/liip/LiipImagineBundle/tree/1.7.2)

*Released on* 2017-02-07 *and assigned* [`1.7.2`](https://github.com/liip/LiipImagineBundle/releases/tag/1.7.2) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.7.1...1.7.2)\).*

- __\[Data Loader\]__ Abstract filesystem resource locator and legacy insecure locator implementation. [\#866](https://github.com/liip/LiipImagineBundle/pull/866) *([robfrawley](https://github.com/robfrawley))*
- __\[Minor\]__ __\[Data Loader\]__ Fix for FileSystemLoader annotation. [\#868](https://github.com/liip/LiipImagineBundle/pull/868) *([tgabi333](https://github.com/tgabi333))*
- __\[Dependency Injection\]__ Container logging for compiler passes. [\#867](https://github.com/liip/LiipImagineBundle/pull/867) *([robfrawley](https://github.com/robfrawley))*
- __\[CI\]__ Use Prestissimo package for Travis build. [\#864](https://github.com/liip/LiipImagineBundle/pull/864) *([robfrawley](https://github.com/robfrawley))*
- __\[GitHub\]__ Add Github templates for issues and PRs. [\#863](https://github.com/liip/LiipImagineBundle/pull/863) *([robfrawley](https://github.com/robfrawley))*
- __\[Symfony\]__ Bug fixes and deprecation cleanup for Symfony 3.3. [\#860](https://github.com/liip/LiipImagineBundle/pull/860) *([robfrawley](https://github.com/robfrawley))*
- __\[Filter\]__ Upscale filter should use the highest dimension to calculate ratio. [\#856](https://github.com/liip/LiipImagineBundle/pull/856) *([Rattler3](https://github.com/Rattler3))*


## [v1.7.1](https://github.com/liip/LiipImagineBundle/tree/1.7.1)

*Released on* 2017-01-19 *and assigned* [`1.7.1`](https://github.com/liip/LiipImagineBundle/releases/tag/1.7.1) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.7.0...1.7.1)\).*

- __\[Data Loader\]__ Allow multiple root paths for FileSystemLoader. [\#851](https://github.com/liip/LiipImagineBundle/pull/851) *([robfrawley](https://github.com/robfrawley))*
- __\[Documentation\]__ Fix strange wording in readme. [\#847](https://github.com/liip/LiipImagineBundle/pull/847) *([svenluijten](https://github.com/svenluijten))*


## [v1.7.0](https://github.com/liip/LiipImagineBundle/tree/1.7.0)

*Released on* 2017-01-08 *and assigned* [`1.7.0`](https://github.com/liip/LiipImagineBundle/releases/tag/1.7.0) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.6.0...1.7.0)\).*

- __\[Dependency Injection\]__ Use DefaultMetadataReader instead of ExifMetadataReader when "exif" extension is not present. [\#841](https://github.com/liip/LiipImagineBundle/pull/841) *([cedricziel](https://github.com/cedricziel))*
- __\[Documentation\]__ Updating twig call to utilise asset\(\) \(to match README.md\) \(closes [\#830](https://github.com/liip/LiipImagineBundle/issues/830)\). [\#836](https://github.com/liip/LiipImagineBundle/pull/836) *([antoligy](https://github.com/antoligy))*
- __\[Composer\]__ Exclude "Tests" directory from classmap. [\#835](https://github.com/liip/LiipImagineBundle/pull/835) *([pamil](https://github.com/pamil))*
- __\[Composer\]__ Require components that no longer ship with Symfony FrameworkBundle 3.2. [\#832](https://github.com/liip/LiipImagineBundle/pull/832) *([rpkamp](https://github.com/rpkamp))*
- __\[Documentation\]__ Document how web paths are built. [\#829](https://github.com/liip/LiipImagineBundle/pull/829) *([greg0ire](https://github.com/greg0ire))*
- __\[Documentation\]__ Wrap relative path with asset\(\) Twig function. [\#825](https://github.com/liip/LiipImagineBundle/pull/825) *([bocharsky-bw](https://github.com/bocharsky-bw))*
- __\[Documentation\]__ __\[Data Loader\]__ Update custom data loader tag. [\#821](https://github.com/liip/LiipImagineBundle/pull/821) *([IllesAprod](https://github.com/IllesAprod))*
- __\[Documentation\]__ Fix typo in README.md example code. [\#819](https://github.com/liip/LiipImagineBundle/pull/819) *([redjanym](https://github.com/redjanym))*
- __\[Documentation\]__ Fix RST indentation error in AWS S3 cache resolver documentation. [\#809](https://github.com/liip/LiipImagineBundle/pull/809) *([GeoffreyHervet](https://github.com/GeoffreyHervet))*
- __\[Documentation\]__ Fix typo in basic-usage.rst example code. [\#805](https://github.com/liip/LiipImagineBundle/pull/805) *([you-ser](https://github.com/you-ser))*
- __\[Documentation\]__ Fix missing "data_loader" option in Flyststem code example. [\#803](https://github.com/liip/LiipImagineBundle/pull/803) *([davidfuhr](https://github.com/davidfuhr))*
- __\[Documentation\]__ Typo/Fix/Clarification for Watermark RST Docs. [\#802](https://github.com/liip/LiipImagineBundle/pull/802) *([robfrawley](https://github.com/robfrawley))*
- __\[CI\]__ Apply latest style rules to entire codebase. [\#800](https://github.com/liip/LiipImagineBundle/pull/800) *([robfrawley](https://github.com/robfrawley))*
- __\[Minor\]__ __\[CI\]__ __\[Travis\]__ Bugfix: remove short array syntax and fix merge error. [\#799](https://github.com/liip/LiipImagineBundle/pull/799) *([robfrawley](https://github.com/robfrawley))*
- __\[Travis\]__ Add Symfony Framework 3.1.x and 3.2-dev to build matrix. [\#796](https://github.com/liip/LiipImagineBundle/pull/796) *([cedricziel](https://github.com/cedricziel))*
- __\[Config\]__ Add visibility argument to service definition. [\#795](https://github.com/liip/LiipImagineBundle/pull/795) *([cedricziel](https://github.com/cedricziel))*
- __\[Tests\]__ Bugfix for failing tests \(introduced in [\#777](https://github.com/liip/LiipImagineBundle/issues/777)\). [\#793](https://github.com/liip/LiipImagineBundle/pull/793) *([robfrawley](https://github.com/robfrawley))*
- __\[CI\]__ Added php\_cs.dist and updated .styleci.yml / Fixed and updated .travis.yml. [\#792](https://github.com/liip/LiipImagineBundle/pull/792) *([robfrawley](https://github.com/robfrawley))*
- __\[Documentation\]__ Add LICENSE.md. [\#790](https://github.com/liip/LiipImagineBundle/pull/790) *([robfrawley](https://github.com/robfrawley))*
- __\[Documentation\]__ Major update/refactoring and additions to README.md and RST documentation. [\#789](https://github.com/liip/LiipImagineBundle/pull/789) *([robfrawley](https://github.com/robfrawley))*
- __\[Documentation\]__ Updated CHANGELOG.md. [\#788](https://github.com/liip/LiipImagineBundle/pull/788) *([robfrawley](https://github.com/robfrawley))*
- __\[Filter\]__ __\[Tests\]__ Fix "list" usages and use "getMockBuilder()" \(closes [\#731](https://github.com/liip/LiipImagineBundle/issues/731)\). [\#787](https://github.com/liip/LiipImagineBundle/pull/787) *([antoligy](https://github.com/antoligy))*
- __\[Data Loader\]__ Cleanup FileSystemLoader implementation and add tests \(followup to [\#775](https://github.com/liip/LiipImagineBundle/issues/775)\). [\#785](https://github.com/liip/LiipImagineBundle/pull/785) *([robfrawley](https://github.com/robfrawley))*
- __\[Post Processor\]__ Add "temp_dir" option for post-processors. [\#779](https://github.com/liip/LiipImagineBundle/pull/779) *([jehaby](https://github.com/jehaby))*
- __\[Cache Resolver\]__ Add visibility argument to flysystem resolver. [\#777](https://github.com/liip/LiipImagineBundle/pull/777) *([cedricziel](https://github.com/cedricziel))*
- __\[Data Loader\]__ Fix FileSystemLoader path resolution handlers and outside root check. [\#775](https://github.com/liip/LiipImagineBundle/pull/775) *([robfrawley](https://github.com/robfrawley))*
- __\[Filter\]__ Make Downscale and Upscale derivatives of Scale. [\#773](https://github.com/liip/LiipImagineBundle/pull/773) *([deviprsd21](https://github.com/deviprsd21))*
- __\[CI\]__ Applied fixes from StyleCI. [\#768](https://github.com/liip/LiipImagineBundle/pull/768) *([lsmith77](https://github.com/lsmith77))*
- __\[DI\]__ Replaced deprecated factory\_class and factory\_method. [\#767](https://github.com/liip/LiipImagineBundle/pull/767) *([rvanlaarhoven](https://github.com/rvanlaarhoven))*
- __\[Documentation\]__ Update basic-usage.rst. [\#766](https://github.com/liip/LiipImagineBundle/pull/766) *([nochecksum](https://github.com/nochecksum))*
- __\[Post Processor\]__ Implemented ConfigurablePostProcessorInterface in OptiPngPostProcessor. [\#764](https://github.com/liip/LiipImagineBundle/pull/764) *([jehaby](https://github.com/jehaby))*


## [v1.6.0](https://github.com/liip/LiipImagineBundle/tree/1.6.0)

*Released on* 2016-07-22 *and assigned* [`1.6.0`](https://github.com/liip/LiipImagineBundle/releases/tag/1.6.0) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.5.3...1.6.0)\).*

- Input is added twice in the OptiPngProcessor. [\#762](https://github.com/liip/LiipImagineBundle/pull/762) *([antoligy](https://github.com/antoligy))*
- Enable configuration of post processors using parameters \(closes [\#720](https://github.com/liip/LiipImagineBundle/issues/720)\). [\#759](https://github.com/liip/LiipImagineBundle/pull/759) *([antoligy](https://github.com/antoligy))*
- Applied fixes from StyleCI. [\#758](https://github.com/liip/LiipImagineBundle/pull/758) *([lsmith77](https://github.com/lsmith77))*
- Applied fixes from StyleCI. [\#757](https://github.com/liip/LiipImagineBundle/pull/757) *([lsmith77](https://github.com/lsmith77))*
- Add configuration options for jpegoptim post-processor. [\#756](https://github.com/liip/LiipImagineBundle/pull/756) *([dylanschoenmakers](https://github.com/dylanschoenmakers))*
- Ignore invalid exif orientations. [\#751](https://github.com/liip/LiipImagineBundle/pull/751) *([lstrojny](https://github.com/lstrojny))*
- Quote strings starting '%' in YAML. [\#745](https://github.com/liip/LiipImagineBundle/pull/745) *([jaikdean](https://github.com/jaikdean))*
- Fix tempnam usages. [\#723](https://github.com/liip/LiipImagineBundle/pull/723) *([1ed](https://github.com/1ed))*
- Background filter: allow image positioning. [\#721](https://github.com/liip/LiipImagineBundle/pull/721) *([uvoelkel](https://github.com/uvoelkel))*
- Add Flysystem resolver. [\#715](https://github.com/liip/LiipImagineBundle/pull/715) *([cedricziel](https://github.com/cedricziel))*
- Downscale filter scales an image to fit bounding box. [\#696](https://github.com/liip/LiipImagineBundle/pull/696) *([aminin](https://github.com/aminin))*
- Implement Imagine Grayscale filter. [\#638](https://github.com/liip/LiipImagineBundle/pull/638) *([gregumo](https://github.com/gregumo))*


## [v1.5.3](https://github.com/liip/LiipImagineBundle/tree/1.5.3)

*Released on* 2016-05-06 *and assigned* [`1.5.3`](https://github.com/liip/LiipImagineBundle/releases/tag/1.5.3) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.5.2...1.5.3)\).*

- Add @Event annotation to let IDEs known event names and class instance. [\#732](https://github.com/liip/LiipImagineBundle/pull/732) *([Haehnchen](https://github.com/Haehnchen))*
- Introduce mozjpeg and pngquant post-processors, add transform options. [\#717](https://github.com/liip/LiipImagineBundle/pull/717) *([antoligy](https://github.com/antoligy))*
- StreamLoader-exception-arguments. [\#714](https://github.com/liip/LiipImagineBundle/pull/714) *([antonsmolin](https://github.com/antonsmolin))*


## [v1.5.2](https://github.com/liip/LiipImagineBundle/tree/1.5.2)

*Released on* 2016-02-16 *and assigned* [`1.5.2`](https://github.com/liip/LiipImagineBundle/releases/tag/1.5.2) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.5.1...1.5.2)\).*

- Revert "Merge pull request [\#699](https://github.com/liip/LiipImagineBundle/issues/699) from jockri/fix-background-filter". [\#709](https://github.com/liip/LiipImagineBundle/pull/709) *([mangelsnc](https://github.com/mangelsnc))*


## [v1.5.1](https://github.com/liip/LiipImagineBundle/tree/1.5.1)

*Released on* 2016-02-13 *and assigned* [`1.5.1`](https://github.com/liip/LiipImagineBundle/releases/tag/1.5.1) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.5.0...1.5.1)\).*

- Fix regression introduced in [\#bb8e410](https://github.com/liip/LiipImagineBundle/commit/bb8e4109672902e37931e0a491ff55ebac93d8e9). [\#707](https://github.com/liip/LiipImagineBundle/pull/707) *([Seldaek](https://github.com/Seldaek))*


## [v1.5.0](https://github.com/liip/LiipImagineBundle/tree/1.5.0)

*Released on* 2016-02-12 *and assigned* [`1.5.0`](https://github.com/liip/LiipImagineBundle/releases/tag/1.5.0) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.4.3...1.5.0)\).*

- Applied fixes from StyleCI. [\#706](https://github.com/liip/LiipImagineBundle/pull/706) *([lsmith77](https://github.com/lsmith77))*
- Add FileBinaryInterface to support large files without loading them in memory unnecessarily. [\#705](https://github.com/liip/LiipImagineBundle/pull/705) *([Seldaek](https://github.com/Seldaek))*
- Fix background filter. [\#699](https://github.com/liip/LiipImagineBundle/pull/699) *([jockri](https://github.com/jockri))*
- Fix undeclared variable. [\#697](https://github.com/liip/LiipImagineBundle/pull/697) *([tifabien](https://github.com/tifabien))*
- Update WebPathResolver.php. [\#695](https://github.com/liip/LiipImagineBundle/pull/695) *([gonzalovilaseca](https://github.com/gonzalovilaseca))*
- Add missing link to the filters doc. [\#694](https://github.com/liip/LiipImagineBundle/pull/694) *([bocharsky-bw](https://github.com/bocharsky-bw))*
- Adding optipng post transformer. [\#692](https://github.com/liip/LiipImagineBundle/pull/692) *([gouaille](https://github.com/gouaille))*


## [v1.4.3](https://github.com/liip/LiipImagineBundle/tree/1.4.3)

*Released on* 2016-01-14 *and assigned* [`1.4.3`](https://github.com/liip/LiipImagineBundle/releases/tag/1.4.3) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.4.2...1.4.3)\).*

- Fixed build issues. [\#691](https://github.com/liip/LiipImagineBundle/pull/691) *([yceruto](https://github.com/yceruto))*
- Fixed doc errors reported by docs build tool. [\#690](https://github.com/liip/LiipImagineBundle/pull/690) *([javiereguiluz](https://github.com/javiereguiluz))*
- Explicit attr definition was added. [\#688](https://github.com/liip/LiipImagineBundle/pull/688) *([ostretsov](https://github.com/ostretsov))*
- Flysystem support added. [\#674](https://github.com/liip/LiipImagineBundle/pull/674) *([graundas](https://github.com/graundas))*


## [v1.4.2](https://github.com/liip/LiipImagineBundle/tree/1.4.2)

*Released on* 2015-12-29 *and assigned* [`1.4.2`](https://github.com/liip/LiipImagineBundle/releases/tag/1.4.2) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.4.1...1.4.2)\).*

- Proxy resolver allow find and replace and regexp strategies. [\#687](https://github.com/liip/LiipImagineBundle/pull/687) *([makasim](https://github.com/makasim))*
- Added contributing docs. [\#681](https://github.com/liip/LiipImagineBundle/pull/681) *([helios-ag](https://github.com/helios-ag))*
- Rebased commands document patch \(see [\#533](https://github.com/liip/LiipImagineBundle/issues/533)\). [\#680](https://github.com/liip/LiipImagineBundle/pull/680) *([helios-ag](https://github.com/helios-ag))*


## [v1.4.1](https://github.com/liip/LiipImagineBundle/tree/1.4.1)

*Released on* 2015-12-27 *and assigned* [`1.4.1`](https://github.com/liip/LiipImagineBundle/releases/tag/1.4.1) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.4.0...1.4.1)\).*

- Aws sdk v3. [\#685](https://github.com/liip/LiipImagineBundle/pull/685) *([makasim](https://github.com/makasim))*


## [v1.4.0](https://github.com/liip/LiipImagineBundle/tree/1.4.0)

*Released on* 2015-12-27 *and assigned* [`1.4.0`](https://github.com/liip/LiipImagineBundle/releases/tag/1.4.0) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.3.3...1.4.0)\).*

- __\[Resolver\]__ Add ability to force resolver. [\#684](https://github.com/liip/LiipImagineBundle/pull/684) *([makasim](https://github.com/makasim))*


## [v1.3.3](https://github.com/liip/LiipImagineBundle/tree/1.3.3)

*Released on* 2015-12-27 *and assigned* [`1.3.3`](https://github.com/liip/LiipImagineBundle/releases/tag/1.3.3) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.3.2...1.3.3)\).*

- Destruct image to cleanup memory. [\#682](https://github.com/liip/LiipImagineBundle/pull/682) *([cmodijk](https://github.com/cmodijk))*


## [v1.3.2](https://github.com/liip/LiipImagineBundle/tree/1.3.2)

*Released on* 2015-12-10 *and assigned* [`1.3.2`](https://github.com/liip/LiipImagineBundle/releases/tag/1.3.2) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.3.1...1.3.2)\).*

- Removed UrlGenerator deprecations from Symfony 2.8. [\#673](https://github.com/liip/LiipImagineBundle/pull/673) *([sebastianblum](https://github.com/sebastianblum))*
- Typo. [\#668](https://github.com/liip/LiipImagineBundle/pull/668) *([benoitMariaux](https://github.com/benoitMariaux))*
- Misc. fixes and improvements to the docs. [\#667](https://github.com/liip/LiipImagineBundle/pull/667) *([javiereguiluz](https://github.com/javiereguiluz))*
- Skip MongoDB ODM related tests on PHP7 and HHVM. [\#659](https://github.com/liip/LiipImagineBundle/pull/659) *([lsmith77](https://github.com/lsmith77))*
- Fix all test fails in master \(just to check\). [\#658](https://github.com/liip/LiipImagineBundle/pull/658) *([kamazee](https://github.com/kamazee))*
- Fix handling invalid orientation in AutoRotateFilterLoader & test exceptions. [\#657](https://github.com/liip/LiipImagineBundle/pull/657) *([kamazee](https://github.com/kamazee))*
- Fix broken CacheResolver tests \(see [\#650](https://github.com/liip/LiipImagineBundle/issues/650)\). [\#655](https://github.com/liip/LiipImagineBundle/pull/655) *([kamazee](https://github.com/kamazee))*
- Correctly handles all rotations, even those involving flippin. [\#654](https://github.com/liip/LiipImagineBundle/pull/654) *([Heshyo](https://github.com/Heshyo))*
- Incorporate feedback from @WouterJ for PR 651. [\#653](https://github.com/liip/LiipImagineBundle/pull/653) *([kix](https://github.com/kix))*
- Applied fixes from StyleCI. [\#652](https://github.com/liip/LiipImagineBundle/pull/652) *([lsmith77](https://github.com/lsmith77))*
- Add notes on basic usage. [\#651](https://github.com/liip/LiipImagineBundle/pull/651) *([kix](https://github.com/kix))*
- Fix travis php version. [\#649](https://github.com/liip/LiipImagineBundle/pull/649) *([Koc](https://github.com/Koc))*
- Update StreamLoader.php. [\#648](https://github.com/liip/LiipImagineBundle/pull/648) *([kix](https://github.com/kix))*
- Applied fixes from StyleCI. [\#646](https://github.com/liip/LiipImagineBundle/pull/646) *([lsmith77](https://github.com/lsmith77))*
- Updated build matrix. [\#645](https://github.com/liip/LiipImagineBundle/pull/645) *([lsmith77](https://github.com/lsmith77))*
- Fix typo. [\#634](https://github.com/liip/LiipImagineBundle/pull/634) *([trsteel88](https://github.com/trsteel88))*
- Added support for special characters and white spaces in image name. [\#629](https://github.com/liip/LiipImagineBundle/pull/629) *([ivanbarlog](https://github.com/ivanbarlog))*
- Updated docs for features introduced in Symfony 2.4. [\#621](https://github.com/liip/LiipImagineBundle/pull/621) *([foaly-nr1](https://github.com/foaly-nr1))*
- Use identity instead equality. [\#619](https://github.com/liip/LiipImagineBundle/pull/619) *([piotrantosik](https://github.com/piotrantosik))*
- Context parameter cannot be an empty string. [\#618](https://github.com/liip/LiipImagineBundle/pull/618) *([aistis-](https://github.com/aistis-))*
- Introduced DownscaleFilterLoader. [\#610](https://github.com/liip/LiipImagineBundle/pull/610) *([sascha-meissner](https://github.com/sascha-meissner))*


## [v1.3.1](https://github.com/liip/LiipImagineBundle/tree/1.3.1)

*Released on* 2015-08-27 *and assigned* [`1.3.1`](https://github.com/liip/LiipImagineBundle/releases/tag/1.3.1) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.3.0...1.3.1)\).*

- Fix deprecated twig filter syntax. [\#631](https://github.com/liip/LiipImagineBundle/pull/631) *([Rattler3](https://github.com/Rattler3))*
- Fix invalid yaml. [\#623](https://github.com/liip/LiipImagineBundle/pull/623) *([carlcraig](https://github.com/carlcraig))*
- Switch to docker based travis infrastructure. [\#622](https://github.com/liip/LiipImagineBundle/pull/622) *([lsmith77](https://github.com/lsmith77))*
- Return string, not Twig\_Markup object in Twig extension. [\#615](https://github.com/liip/LiipImagineBundle/pull/615) *([lstrojny](https://github.com/lstrojny))*
- Use is\_file\(\) instead of Filesystem::exists\(\). [\#614](https://github.com/liip/LiipImagineBundle/pull/614) *([lstrojny](https://github.com/lstrojny))*
- Make it easier to get a dev environment up and running. [\#613](https://github.com/liip/LiipImagineBundle/pull/613) *([lstrojny](https://github.com/lstrojny))*
- Fix code block into README. [\#608](https://github.com/liip/LiipImagineBundle/pull/608) *([PedroTroller](https://github.com/PedroTroller))*
- Fix upscale size not being calculated correctly. [\#561](https://github.com/liip/LiipImagineBundle/pull/561) *([scuben](https://github.com/scuben))*


## [v1.3.0](https://github.com/liip/LiipImagineBundle/tree/1.3.0)

*Released on* 2015-06-04 *and assigned* [`1.3.0`](https://github.com/liip/LiipImagineBundle/releases/tag/1.3.0) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.2.7...1.3.0)\).*

- Use setFactory service definition method for Symfony \>= 2.6 \(when possible\). [\#566](https://github.com/liip/LiipImagineBundle/pull/566) *([adam187](https://github.com/adam187))*


## [v1.2.7](https://github.com/liip/LiipImagineBundle/tree/1.2.7)

*Released on* 2015-06-02 *and assigned* [`1.2.7`](https://github.com/liip/LiipImagineBundle/releases/tag/1.2.7) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.2.6...1.2.7)\).*

- Make AwsS3Resolver compatible with SDK v3. [\#605](https://github.com/liip/LiipImagineBundle/pull/605) *([cdaguerre](https://github.com/cdaguerre))*
- __\[Documentation\]__ Add missing coma and fix indentation in README.md. [\#604](https://github.com/liip/LiipImagineBundle/pull/604) *([grena](https://github.com/grena))*
- Removed TransformerInterface. [\#603](https://github.com/liip/LiipImagineBundle/pull/603) *([rvanlaarhoven](https://github.com/rvanlaarhoven))*
- Remove duplicate parameter. [\#601](https://github.com/liip/LiipImagineBundle/pull/601) *([ip512](https://github.com/ip512))*
- Fix typo. [\#600](https://github.com/liip/LiipImagineBundle/pull/600) *([hpatoio](https://github.com/hpatoio))*
- Adding details to use the bundle with remote images. [\#569](https://github.com/liip/LiipImagineBundle/pull/569) *([flug](https://github.com/flug))*


## [v1.2.6](https://github.com/liip/LiipImagineBundle/tree/1.2.6)

*Released on* 2015-04-24 *and assigned* [`1.2.6`](https://github.com/liip/LiipImagineBundle/releases/tag/1.2.6) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.2.5...1.2.6)\).*

- Check $filters is an array. [\#596](https://github.com/liip/LiipImagineBundle/pull/596) *([trsteel88](https://github.com/trsteel88))*


## [v1.2.5](https://github.com/liip/LiipImagineBundle/tree/1.2.5)

*Released on* 2015-04-08 *and assigned* [`1.2.5`](https://github.com/liip/LiipImagineBundle/releases/tag/1.2.5) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.2.4...1.2.5)\).*

- Add image rotate filter. [\#588](https://github.com/liip/LiipImagineBundle/pull/588) *([bocharsky-bw](https://github.com/bocharsky-bw))*
- Run php-cs-fixer on bundle. [\#583](https://github.com/liip/LiipImagineBundle/pull/583) *([trsteel88](https://github.com/trsteel88))*
- Fix typo. [\#582](https://github.com/liip/LiipImagineBundle/pull/582) *([bicpi](https://github.com/bicpi))*
- Fix typos. [\#581](https://github.com/liip/LiipImagineBundle/pull/581) *([bicpi](https://github.com/bicpi))*
- Fix typos. [\#580](https://github.com/liip/LiipImagineBundle/pull/580) *([bicpi](https://github.com/bicpi))*


## [v1.2.4](https://github.com/liip/LiipImagineBundle/tree/1.2.4)

*Released on* 2015-03-27 *and assigned* [`1.2.4`](https://github.com/liip/LiipImagineBundle/releases/tag/1.2.4) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.2.3...1.2.4)\).*

- Update how missing filters are logged. [\#579](https://github.com/liip/LiipImagineBundle/pull/579) *([trsteel88](https://github.com/trsteel88))*
- Use isDefined method for OptionsResolver instead of isKnown  \(when possible\). [\#567](https://github.com/liip/LiipImagineBundle/pull/567) *([adam187](https://github.com/adam187))*


## [v1.2.3](https://github.com/liip/LiipImagineBundle/tree/1.2.3)

*Released on* 2015-02-22 *and assigned* [`1.2.3`](https://github.com/liip/LiipImagineBundle/releases/tag/1.2.3) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.2.2...1.2.3)\).*

- Fix invalid in\_array. [\#565](https://github.com/liip/LiipImagineBundle/pull/565) *([digitalkaoz](https://github.com/digitalkaoz))*
- Add a short introductory paragraph about the bundle. [\#559](https://github.com/liip/LiipImagineBundle/pull/559) *([javiereguiluz](https://github.com/javiereguiluz))*
- Update Filters.rst. [\#556](https://github.com/liip/LiipImagineBundle/pull/556) *([Spawnrad](https://github.com/Spawnrad))*
- Fixed the syntax of the internal doc links. [\#554](https://github.com/liip/LiipImagineBundle/pull/554) *([javiereguiluz](https://github.com/javiereguiluz))*
- Updated README.md to point to new .rst doc files. [\#551](https://github.com/liip/LiipImagineBundle/pull/551) *([Khez](https://github.com/Khez))*
- Fix typo on readme file. [\#550](https://github.com/liip/LiipImagineBundle/pull/550) *([erivello](https://github.com/erivello))*
- Switched the documentation from Markdown to ReStructuredText. [\#545](https://github.com/liip/LiipImagineBundle/pull/545) *([javiereguiluz](https://github.com/javiereguiluz))*
- Fix Filter Documentation. [\#544](https://github.com/liip/LiipImagineBundle/pull/544) *([wodka](https://github.com/wodka))*
- Add support for the new quality options. [\#473](https://github.com/liip/LiipImagineBundle/pull/473) *([patrickli](https://github.com/patrickli))*


## [v1.2.2](https://github.com/liip/LiipImagineBundle/tree/1.2.2)

*Released on* 2015-01-08 *and assigned* [`1.2.2`](https://github.com/liip/LiipImagineBundle/releases/tag/1.2.2) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.2.1...1.2.2)\).*

- Update the filter\_sets Documentation about removed configurations. [\#543](https://github.com/liip/LiipImagineBundle/pull/543) *([mbiagetti](https://github.com/mbiagetti))*
- Implement interlace filter. [\#503](https://github.com/liip/LiipImagineBundle/pull/503) *([wodka](https://github.com/wodka))*


## [v1.2.1](https://github.com/liip/LiipImagineBundle/tree/1.2.1)

*Released on* 2014-12-10 *and assigned* [`1.2.1`](https://github.com/liip/LiipImagineBundle/releases/tag/1.2.1) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.2.0...1.2.1)\).*

- Argument to s3 resolver prototype definition has been added. [\#536](https://github.com/liip/LiipImagineBundle/pull/536) *([ruslan-polutsygan](https://github.com/ruslan-polutsygan))*


## [v1.2.0](https://github.com/liip/LiipImagineBundle/tree/1.2.0)

*Released on* 2014-12-10 *and assigned* [`1.2.0`](https://github.com/liip/LiipImagineBundle/releases/tag/1.2.0) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.1.1...1.2.0)\).*

- S3 resolver put options. [\#535](https://github.com/liip/LiipImagineBundle/pull/535) *([ruslan-polutsygan](https://github.com/ruslan-polutsygan))*
- __\[Minor\]__ Fixed PHPDoc. [\#528](https://github.com/liip/LiipImagineBundle/pull/528) *([sdaoudi](https://github.com/sdaoudi))*


## [v1.1.1](https://github.com/liip/LiipImagineBundle/tree/1.1.1)

*Released on* 2014-11-12 *and assigned* [`1.1.1`](https://github.com/liip/LiipImagineBundle/releases/tag/1.1.1) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.1.0...1.1.1)\).*

- Fix crash when no post processor is defined. [\#526](https://github.com/liip/LiipImagineBundle/pull/526) *([lolautruche](https://github.com/lolautruche))*
- __\[Cache Resolver\]__ Sanitize URL to directory name in web path resolved. [\#480](https://github.com/liip/LiipImagineBundle/pull/480) *([teohhanhui](https://github.com/teohhanhui))*


## [v1.1.0](https://github.com/liip/LiipImagineBundle/tree/1.1.0)

*Released on* 2014-10-29 *and assigned* [`1.1.0`](https://github.com/liip/LiipImagineBundle/releases/tag/1.1.0) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.8...1.1.0)\).*

- __\[Post Processor\]__ Handlers to be applied on filtered image binary. [\#519](https://github.com/liip/LiipImagineBundle/pull/519) *([kostiklv](https://github.com/kostiklv))*


## [v1.0.8](https://github.com/liip/LiipImagineBundle/tree/1.0.8)

*Released on* 2014-10-22 *and assigned* [`1.0.8`](https://github.com/liip/LiipImagineBundle/releases/tag/1.0.8) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.7...1.0.8)\).*

- Delete АГГЗ.jpeg. [\#515](https://github.com/liip/LiipImagineBundle/pull/515) *([crash21](https://github.com/crash21))*
- Update configuration.md. [\#513](https://github.com/liip/LiipImagineBundle/pull/513) *([hugohenrique](https://github.com/hugohenrique))*


## [v1.0.7](https://github.com/liip/LiipImagineBundle/tree/1.0.7)

*Released on* 2014-10-18 *and assigned* [`1.0.7`](https://github.com/liip/LiipImagineBundle/releases/tag/1.0.7) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.6...1.0.7)\).*

- Fix tests, upgrade phpunit up to 4.3. [\#511](https://github.com/liip/LiipImagineBundle/pull/511) *([makasim](https://github.com/makasim))*
- Image default when notloadable exception. [\#510](https://github.com/liip/LiipImagineBundle/pull/510) *([Neime](https://github.com/Neime))*
- Explain how to change the default resolver. [\#508](https://github.com/liip/LiipImagineBundle/pull/508) *([dbu](https://github.com/dbu))*
- Updated DI configuration to the current implementation of the loader. [\#500](https://github.com/liip/LiipImagineBundle/pull/500) *([peterrehm](https://github.com/peterrehm))*
- Support custom output format for each filter set. [\#477](https://github.com/liip/LiipImagineBundle/pull/477) *([teohhanhui](https://github.com/teohhanhui))*


## [v1.0.6](https://github.com/liip/LiipImagineBundle/tree/1.0.6)

*Released on* 2014-09-17 *and assigned* [`1.0.6`](https://github.com/liip/LiipImagineBundle/releases/tag/1.0.6) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.5...1.0.6)\).*

- Fix GridFSLoader. [\#461](https://github.com/liip/LiipImagineBundle/pull/461) *([aldeck](https://github.com/aldeck))*


## [v1.0.5](https://github.com/liip/LiipImagineBundle/tree/1.0.5)

*Released on* 2014-09-15 *and assigned* [`1.0.5`](https://github.com/liip/LiipImagineBundle/releases/tag/1.0.5) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.4...1.0.5)\).*

- Check if runtimeconfig path is stored. [\#498](https://github.com/liip/LiipImagineBundle/pull/498) *([trsteel88](https://github.com/trsteel88))*
- Update README.md. [\#490](https://github.com/liip/LiipImagineBundle/pull/490) *([JellyBellyDev](https://github.com/JellyBellyDev))*
- Update README.md. [\#488](https://github.com/liip/LiipImagineBundle/pull/488) *([JellyBellyDev](https://github.com/JellyBellyDev))*
- Fix auto rotate. [\#476](https://github.com/liip/LiipImagineBundle/pull/476) *([scuben](https://github.com/scuben))*
- Support animated gif. [\#466](https://github.com/liip/LiipImagineBundle/pull/466) *([scuben](https://github.com/scuben))*


## [v1.0.4](https://github.com/liip/LiipImagineBundle/tree/1.0.4)

*Released on* 2014-07-30 *and assigned* [`1.0.4`](https://github.com/liip/LiipImagineBundle/releases/tag/1.0.4) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.3...1.0.4)\).*

- Update WebPathResolverFactory.php. [\#467](https://github.com/liip/LiipImagineBundle/pull/467) *([JJK801](https://github.com/JJK801))*


## [v1.0.3](https://github.com/liip/LiipImagineBundle/tree/1.0.3)

*Released on* 2014-07-30 *and assigned* [`1.0.3`](https://github.com/liip/LiipImagineBundle/releases/tag/1.0.3) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.2...1.0.3)\).*

- Fixing issue with removed class Color. [\#458](https://github.com/liip/LiipImagineBundle/pull/458) *([lstrojny](https://github.com/lstrojny))*
- Added PHP 5.6 and HHVM to travis.yml. [\#454](https://github.com/liip/LiipImagineBundle/pull/454) *([Nyholm](https://github.com/Nyholm))*
- Make the Bundle compatible with config:dump-reference command. [\#452](https://github.com/liip/LiipImagineBundle/pull/452) *([lsmith77](https://github.com/lsmith77))*


## [v1.0.2](https://github.com/liip/LiipImagineBundle/tree/1.0.2)

*Released on* 2014-06-24 *and assigned* [`1.0.2`](https://github.com/liip/LiipImagineBundle/releases/tag/1.0.2) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.1...1.0.2)\).*

- Update README.md. [\#447](https://github.com/liip/LiipImagineBundle/pull/447) *([sgaze](https://github.com/sgaze))*
- Update configuration.md. [\#446](https://github.com/liip/LiipImagineBundle/pull/446) *([sgaze](https://github.com/sgaze))*


## [v1.0.1](https://github.com/liip/LiipImagineBundle/tree/1.0.1)

*Released on* 2014-06-06 *and assigned* [`1.0.1`](https://github.com/liip/LiipImagineBundle/releases/tag/1.0.1) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.0...1.0.1)\).*

- __\[Stream\]__ throws exception when content cannot be read. [\#444](https://github.com/liip/LiipImagineBundle/pull/444) *([makasim](https://github.com/makasim))*
- Remove unused use-statement and fix phpdoc. [\#441](https://github.com/liip/LiipImagineBundle/pull/441) *([UFOMelkor](https://github.com/UFOMelkor))*


## [v1.0.0](https://github.com/liip/LiipImagineBundle/tree/1.0.0)

*Released on* 2014-05-22 *and assigned* [`1.0.0`](https://github.com/liip/LiipImagineBundle/releases/tag/1.0.0) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.0-alpha7...1.0.0)\).*

- Added possibility to use imagine new metadata api. [\#413](https://github.com/liip/LiipImagineBundle/pull/413) *([digitalkaoz](https://github.com/digitalkaoz))*


## [v1.0.0-alpha7](https://github.com/liip/LiipImagineBundle/tree/1.0.0-alpha7)

*Released on* 2014-05-22 *and assigned* [`1.0.0-alpha7`](https://github.com/liip/LiipImagineBundle/releases/tag/1.0.0-alpha7) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.0-alpha6...1.0.0-alpha7)\).*

- Add a Signer Utility to sign filters, run php-cs-fixer on bundle. [\#405](https://github.com/liip/LiipImagineBundle/pull/405) *([trsteel88](https://github.com/trsteel88))*


## [v1.0.0-alpha6](https://github.com/liip/LiipImagineBundle/tree/1.0.0-alpha6)

*Released on* 2014-05-05 *and assigned* [`1.0.0-alpha6`](https://github.com/liip/LiipImagineBundle/releases/tag/1.0.0-alpha6) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.0-alpha5...1.0.0-alpha6)\).*

- __\[Router\]__ remove custom route loader. [\#425](https://github.com/liip/LiipImagineBundle/pull/425) *([makasim](https://github.com/makasim))*


## [v1.0.0-alpha5](https://github.com/liip/LiipImagineBundle/tree/1.0.0-alpha5)

*Released on* 2014-04-29 *and assigned* [`1.0.0-alpha5`](https://github.com/liip/LiipImagineBundle/releases/tag/1.0.0-alpha5) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.0-alpha4...1.0.0-alpha5)\).*

- Added scrutinizer config. [\#420](https://github.com/liip/LiipImagineBundle/pull/420) *([digitalkaoz](https://github.com/digitalkaoz))*
- Fixed testsuite \(see [\#417](https://github.com/liip/LiipImagineBundle/issues/417) and [\#403](https://github.com/liip/LiipImagineBundle/issues/403)\). [\#419](https://github.com/liip/LiipImagineBundle/pull/419) *([ama3ing](https://github.com/ama3ing))*
- Increase test coverage report. [\#417](https://github.com/liip/LiipImagineBundle/pull/417) *([digitalkaoz](https://github.com/digitalkaoz))*
- Enabled Symfony 2.4 on travis. [\#416](https://github.com/liip/LiipImagineBundle/pull/416) *([digitalkaoz](https://github.com/digitalkaoz))*
- Update configuration.md. [\#410](https://github.com/liip/LiipImagineBundle/pull/410) *([ama3ing](https://github.com/ama3ing))*
- __\[CI\]__ run tests only on 2.3 version. [\#407](https://github.com/liip/LiipImagineBundle/pull/407) *([makasim](https://github.com/makasim))*
- Watermark filter documentation update \(fixes [\#404](https://github.com/liip/LiipImagineBundle/issues/404)\). [\#406](https://github.com/liip/LiipImagineBundle/pull/406) *([ama3ing](https://github.com/ama3ing))*
- Replace NotFoundHttpException with SourceNotFoundException \(fixes [\#373](https://github.com/liip/LiipImagineBundle/issues/373)\). [\#403](https://github.com/liip/LiipImagineBundle/pull/403) *([ama3ing](https://github.com/ama3ing))*
- Removed unreachable statement. [\#402](https://github.com/liip/LiipImagineBundle/pull/402) *([ama3ing](https://github.com/ama3ing))*
- Trim of forwarding slash in path \(fix for [\#369](https://github.com/liip/LiipImagineBundle/issues/369)\). [\#401](https://github.com/liip/LiipImagineBundle/pull/401) *([ama3ing](https://github.com/ama3ing))*


## [v1.0.0-alpha4](https://github.com/liip/LiipImagineBundle/tree/1.0.0-alpha4)

*Released on* 2014-04-14 *and assigned* [`1.0.0-alpha4`](https://github.com/liip/LiipImagineBundle/releases/tag/1.0.0-alpha4) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.0-alpha3...1.0.0-alpha4)\).*

- __\[Config\]__ correctly process resolvers\loaders section if not array or null. [\#396](https://github.com/liip/LiipImagineBundle/pull/396) *([makasim](https://github.com/makasim))*
- Wrong image path \(see [\#368](https://github.com/liip/LiipImagineBundle/issues/368)\). [\#395](https://github.com/liip/LiipImagineBundle/pull/395) *([serdyuka](https://github.com/serdyuka))*


## [v1.0.0-alpha3](https://github.com/liip/LiipImagineBundle/tree/1.0.0-alpha3)

*Released on* 2014-04-14 *and assigned* [`1.0.0-alpha3`](https://github.com/liip/LiipImagineBundle/releases/tag/1.0.0-alpha3) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.0-alpha2...1.0.0-alpha3)\).*

- Added proxy to aws s3 resolver factory. [\#392](https://github.com/liip/LiipImagineBundle/pull/392) *([serdyuka](https://github.com/serdyuka))*


## [v1.0.0-alpha2](https://github.com/liip/LiipImagineBundle/tree/1.0.0-alpha2)

*Released on* 2014-04-10 *and assigned* [`1.0.0-alpha2`](https://github.com/liip/LiipImagineBundle/releases/tag/1.0.0-alpha2) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.0-alpha1...1.0.0-alpha2)\).*

- Documentation update \(fixes [\#389](https://github.com/liip/LiipImagineBundle/issues/389)\). [\#390](https://github.com/liip/LiipImagineBundle/pull/390) *([ama3ing](https://github.com/ama3ing))*
- __\[WIP\]__ Added resolve events to cache manager. [\#388](https://github.com/liip/LiipImagineBundle/pull/388) *([serdyuka](https://github.com/serdyuka))*


## [v1.0.0-alpha1](https://github.com/liip/LiipImagineBundle/tree/1.0.0-alpha1)

*Released on* 2014-04-07 *and assigned* [`1.0.0-alpha1`](https://github.com/liip/LiipImagineBundle/releases/tag/1.0.0-alpha1) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/v0.21.1...1.0.0-alpha1)\).*

- Remove cli command. [\#387](https://github.com/liip/LiipImagineBundle/pull/387) *([serdyuka](https://github.com/serdyuka))*
- Fixed and improved tests for resolve cache command. [\#386](https://github.com/liip/LiipImagineBundle/pull/386) *([serdyuka](https://github.com/serdyuka))*
- __\[Config\]__ Fix default loader not found bug. [\#385](https://github.com/liip/LiipImagineBundle/pull/385) *([makasim](https://github.com/makasim))*
- Resolve command few paths. [\#383](https://github.com/liip/LiipImagineBundle/pull/383) *([serdyuka](https://github.com/serdyuka))*
- Move data loaders to binary folder. [\#382](https://github.com/liip/LiipImagineBundle/pull/382) *([serdyuka](https://github.com/serdyuka))*
- Documentation for cli command. [\#380](https://github.com/liip/LiipImagineBundle/pull/380) *([serdyuka](https://github.com/serdyuka))*
- Cli command to resolve cache. [\#379](https://github.com/liip/LiipImagineBundle/pull/379) *([serdyuka](https://github.com/serdyuka))*
- Update README.md. [\#374](https://github.com/liip/LiipImagineBundle/pull/374) *([daslicht](https://github.com/daslicht))*
- __\[Data Loader\]__ cleanup filesystem loader, simplify logic, add factory. [\#371](https://github.com/liip/LiipImagineBundle/pull/371) *([makasim](https://github.com/makasim))*
- __\[Cache Resolver\]__ allow configure cache\_prefix via factory. [\#370](https://github.com/liip/LiipImagineBundle/pull/370) *([makasim](https://github.com/makasim))*
- Set web\_path resolver as default if not configured. [\#367](https://github.com/liip/LiipImagineBundle/pull/367) *([makasim](https://github.com/makasim))*
- __\[Config\]__ remove path option. [\#366](https://github.com/liip/LiipImagineBundle/pull/366) *([makasim](https://github.com/makasim))*
- Fixed yaml code block on stream loader documentation. [\#363](https://github.com/liip/LiipImagineBundle/pull/363) *([rvanlaarhoven](https://github.com/rvanlaarhoven))*
- __\[Cache Resolver\]__ Use baseUrl and port while generating image path. [\#362](https://github.com/liip/LiipImagineBundle/pull/362) *([makasim](https://github.com/makasim))*
- Removed cache\_clearer documentation. [\#359](https://github.com/liip/LiipImagineBundle/pull/359) *([rvanlaarhoven](https://github.com/rvanlaarhoven))*
- CacheManager updated. [\#355](https://github.com/liip/LiipImagineBundle/pull/355) *([ossinkine](https://github.com/ossinkine))*
- FilesystemLoader updated. [\#354](https://github.com/liip/LiipImagineBundle/pull/354) *([ossinkine](https://github.com/ossinkine))*
- Update filters.md. [\#346](https://github.com/liip/LiipImagineBundle/pull/346) *([zazoomauro](https://github.com/zazoomauro))*


## [v0.21.1](https://github.com/liip/LiipImagineBundle/tree/v0.21.1)

*Released on* 2014-03-14 *and assigned* [`0.21.1`](https://github.com/liip/LiipImagineBundle/releases/tag/v0.21.1) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/v0.21.0...v0.21.1)\).*


## [v0.21.0](https://github.com/liip/LiipImagineBundle/tree/v0.21.0)

*Released on* 2014-03-14 *and assigned* [`0.21.0`](https://github.com/liip/LiipImagineBundle/releases/tag/v0.21.0) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/v0.20.2...v0.21.0)\).*

- Added reference on how to get image path inside a controller. [\#340](https://github.com/liip/LiipImagineBundle/pull/340) *([ama3ing](https://github.com/ama3ing))*
- Add phpunit as require-dev. [\#339](https://github.com/liip/LiipImagineBundle/pull/339) *([makasim](https://github.com/makasim))*
- Twig helper not escape filter url. [\#337](https://github.com/liip/LiipImagineBundle/pull/337) *([makasim](https://github.com/makasim))*
- Added cache clearing & setting cachePrefix for Aws S3. [\#336](https://github.com/liip/LiipImagineBundle/pull/336) *([rvanlaarhoven](https://github.com/rvanlaarhoven))*
- Merge latest changes in master to develop branch. [\#334](https://github.com/liip/LiipImagineBundle/pull/334) *([makasim](https://github.com/makasim))*
- Update [avalanche123/Imagine](https://github.com/avalanche123/Imagine) to 0.6. [\#330](https://github.com/liip/LiipImagineBundle/pull/330) *([vlastv](https://github.com/vlastv))*
- __\[Config\]__ Cleanup bundle configuration. [\#325](https://github.com/liip/LiipImagineBundle/pull/325) *([makasim](https://github.com/makasim))*
- __\[Filter\]__ Dynamic filters. [\#313](https://github.com/liip/LiipImagineBundle/pull/313) *([makasim](https://github.com/makasim))*


## [v0.20.2](https://github.com/liip/LiipImagineBundle/tree/v0.20.2)

*Released on* 2014-02-20 *and assigned* [`0.20.2`](https://github.com/liip/LiipImagineBundle/releases/tag/v0.20.2) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/v0.20.1...v0.20.2)\).*

- GridFSLoader Bug. [\#331](https://github.com/liip/LiipImagineBundle/pull/331) *([peterrehm](https://github.com/peterrehm))*
- Update filters.md. [\#327](https://github.com/liip/LiipImagineBundle/pull/327) *([herb123456](https://github.com/herb123456))*


## [v0.20.1](https://github.com/liip/LiipImagineBundle/tree/v0.20.1)

*Released on* 2014-02-10 *and assigned* [`0.20.1`](https://github.com/liip/LiipImagineBundle/releases/tag/v0.20.1) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/v0.20.0...v0.20.1)\).*

- Fixed ProxyResolver-\>getBrowserPath. [\#323](https://github.com/liip/LiipImagineBundle/pull/323) *([digitalkaoz](https://github.com/digitalkaoz))*


## [v0.20.0](https://github.com/liip/LiipImagineBundle/tree/v0.20.0)

*Released on* 2014-02-07 *and assigned* [`0.20.0`](https://github.com/liip/LiipImagineBundle/releases/tag/v0.20.0) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/v0.19.0...v0.20.0)\).*

- __\[Cache Resolver\]__ Decouple WebPathResolver from http request. Simplify its logic. [\#320](https://github.com/liip/LiipImagineBundle/pull/320) *([makasim](https://github.com/makasim))*
- Added proxy cache resolver. [\#318](https://github.com/liip/LiipImagineBundle/pull/318) *([digitalkaoz](https://github.com/digitalkaoz))*


## [v0.19.0](https://github.com/liip/LiipImagineBundle/tree/v0.19.0)

*Released on* 2014-02-07 *and assigned* [`0.19.0`](https://github.com/liip/LiipImagineBundle/releases/tag/v0.19.0) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/v0.18.0...v0.19.0)\).*

- Improved exception on generation failure. [\#321](https://github.com/liip/LiipImagineBundle/pull/321) *([digitalkaoz](https://github.com/digitalkaoz))*
- Added background\_image filter. [\#319](https://github.com/liip/LiipImagineBundle/pull/319) *([digitalkaoz](https://github.com/digitalkaoz))*
- Fix tests on current develop branch. [\#316](https://github.com/liip/LiipImagineBundle/pull/316) *([makasim](https://github.com/makasim))*
- __\[Cache Resolver\]__ CacheResolver has to cache isStored method too. [\#308](https://github.com/liip/LiipImagineBundle/pull/308) *([makasim](https://github.com/makasim))*
- __\[Cache Resolver\]__ Improve caches invalidation. [\#304](https://github.com/liip/LiipImagineBundle/pull/304) *([makasim](https://github.com/makasim))*


## [v0.18.0](https://github.com/liip/LiipImagineBundle/tree/v0.18.0)

*Released on* 2014-01-29 *and assigned* [`0.18.0`](https://github.com/liip/LiipImagineBundle/releases/tag/v0.18.0) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/v0.17.1...v0.18.0)\).*

- Added an "auto\_rotate" filter based on exif data. [\#254](https://github.com/liip/LiipImagineBundle/pull/254) *([digitalkaoz](https://github.com/digitalkaoz))*


## [v0.17.1](https://github.com/liip/LiipImagineBundle/tree/v0.17.1)

*Released on* 2014-01-24 *and assigned* [`0.17.1`](https://github.com/liip/LiipImagineBundle/releases/tag/v0.17.1) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/v0.17.0...v0.17.1)\).*

- Fixed missing namespace. [\#306](https://github.com/liip/LiipImagineBundle/pull/306) *([digitalkaoz](https://github.com/digitalkaoz))*
- __\[Cache\]__ cache manager has to use isStored inside getBrowserPath method. [\#303](https://github.com/liip/LiipImagineBundle/pull/303) *([makasim](https://github.com/makasim))*
- __\[Cache Resolver\]__ Use binary on store method call. [\#301](https://github.com/liip/LiipImagineBundle/pull/301) *([makasim](https://github.com/makasim))*
- __\[Filter Manager\]__ make use of binary object. [\#297](https://github.com/liip/LiipImagineBundle/pull/297) *([makasim](https://github.com/makasim))*
- __\[Data Loader\]__ remove deprecated phpcr loader. [\#292](https://github.com/liip/LiipImagineBundle/pull/292) *([makasim](https://github.com/makasim))*
- Rework data loaders. Introduce mime type guesser. [\#291](https://github.com/liip/LiipImagineBundle/pull/291) *([makasim](https://github.com/makasim))*
- __\[Tests\]__ increase code coverage by tests. [\#290](https://github.com/liip/LiipImagineBundle/pull/290) *([makasim](https://github.com/makasim))*
- __\[Logger\]__ use PSR one logger. [\#286](https://github.com/liip/LiipImagineBundle/pull/286) *([makasim](https://github.com/makasim))*
- __\[Cache Resolver\]__ Resolver get rid of get browser path. [\#284](https://github.com/liip/LiipImagineBundle/pull/284) *([makasim](https://github.com/makasim))*
- __\[Tests\]__ use real amazon libs in tests. [\#283](https://github.com/liip/LiipImagineBundle/pull/283) *([makasim](https://github.com/makasim))*
- __\[Cache Resolver\]__ do not expose "targetPath". [\#282](https://github.com/liip/LiipImagineBundle/pull/282) *([makasim](https://github.com/makasim))*
- __\[Cache Resolver\]__ remove request parameter. [\#281](https://github.com/liip/LiipImagineBundle/pull/281) *([makasim](https://github.com/makasim))*


## [v0.17.0](https://github.com/liip/LiipImagineBundle/tree/v0.17.0)

*Released on* 2013-12-04 *and assigned* [`0.17.0`](https://github.com/liip/LiipImagineBundle/releases/tag/v0.17.0) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/v0.16.0...v0.17.0)\).*

- Handle image extensions in doctrine loader. [\#276](https://github.com/liip/LiipImagineBundle/pull/276) *([dbu](https://github.com/dbu))*
- Exclude Tests directory on composer archive. [\#274](https://github.com/liip/LiipImagineBundle/pull/274) *([oziks](https://github.com/oziks))*
- Fix composer require-dev. [\#272](https://github.com/liip/LiipImagineBundle/pull/272) *([havvg](https://github.com/havvg))*
- Update filters.md. [\#267](https://github.com/liip/LiipImagineBundle/pull/267) *([uwej711](https://github.com/uwej711))*
- Add comment for image parameter in watermark filter configuration example. [\#263](https://github.com/liip/LiipImagineBundle/pull/263) *([USvER](https://github.com/USvER))*


## [v0.16.0](https://github.com/liip/LiipImagineBundle/tree/v0.16.0)

*Released on* 2013-09-30 *and assigned* [`0.16.0`](https://github.com/liip/LiipImagineBundle/releases/tag/v0.16.0) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/v0.15.1...v0.16.0)\).*

- Add Upscale filter. [\#248](https://github.com/liip/LiipImagineBundle/pull/248) *([maximecolin](https://github.com/maximecolin))*


## [v0.15.1](https://github.com/liip/LiipImagineBundle/tree/v0.15.1)

*Released on* 2013-09-20 *and assigned* [`0.15.1`](https://github.com/liip/LiipImagineBundle/releases/tag/v0.15.1) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/v0.15.0...v0.15.1)\).*

- Set ContentType of AWS cache object. [\#246](https://github.com/liip/LiipImagineBundle/pull/246) *([eXtreme](https://github.com/eXtreme))*


## [v0.15.0](https://github.com/liip/LiipImagineBundle/tree/v0.15.0)

*Released on* 2013-09-18 *and assigned* [`0.15.0`](https://github.com/liip/LiipImagineBundle/releases/tag/v0.15.0) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/v0.14.0...v0.15.0)\).*

- Deprecate the phpcr loader as CmfMediaBundle provides a better one now. [\#243](https://github.com/liip/LiipImagineBundle/pull/243) *([dbu](https://github.com/dbu))*
- Fix missing filename in exception. [\#240](https://github.com/liip/LiipImagineBundle/pull/240) *([havvg](https://github.com/havvg))*
- Corrected aws-sdk-php link. [\#233](https://github.com/liip/LiipImagineBundle/pull/233) *([javiacei](https://github.com/javiacei))*


## [v0.14.0](https://github.com/liip/LiipImagineBundle/tree/v0.14.0)

*Released on* 2013-08-21 *and assigned* [`0.14.0`](https://github.com/liip/LiipImagineBundle/releases/tag/v0.14.0) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/v0.13.0...v0.14.0)\).*

- Add AwsS3Resolver for new SDK version. [\#227](https://github.com/liip/LiipImagineBundle/pull/227) *([havvg](https://github.com/havvg))*


## [v0.13.0](https://github.com/liip/LiipImagineBundle/tree/v0.13.0)

*Released on* 2013-08-19 *and assigned* [`0.13.0`](https://github.com/liip/LiipImagineBundle/releases/tag/v0.13.0) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/v0.12.0...v0.13.0)\).*

- Watermark loader. [\#222](https://github.com/liip/LiipImagineBundle/pull/222) *([KingCrunch](https://github.com/KingCrunch))*


## [v0.12.0](https://github.com/liip/LiipImagineBundle/tree/v0.12.0)

*Released on* 2013-08-19 *and assigned* [`0.12.0`](https://github.com/liip/LiipImagineBundle/releases/tag/v0.12.0) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/v0.11.1...v0.12.0)\).*

- Update [avalanche123/Imagine](https://github.com/avalanche123/Imagine) to 0.5. [\#221](https://github.com/liip/LiipImagineBundle/pull/221) *([KingCrunch](https://github.com/KingCrunch))*


## [v0.11.1](https://github.com/liip/LiipImagineBundle/tree/v0.11.1)

*Released on* 2013-08-05 *and assigned* [`0.11.1`](https://github.com/liip/LiipImagineBundle/releases/tag/v0.11.1) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/v0.11.0...v0.11.1)\).*

- Added documentation on inset and outbound modes of thumbnail filter Documentation \(see [\#207](https://github.com/liip/LiipImagineBundle/issues/207)\). [\#210](https://github.com/liip/LiipImagineBundle/pull/210) *([rjbijl](https://github.com/rjbijl))*


## [v0.11.0](https://github.com/liip/LiipImagineBundle/tree/v0.11.0)

*Released on* 2013-06-21 *and assigned* [`0.11.0`](https://github.com/liip/LiipImagineBundle/releases/tag/v0.11.0) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/v0.10.1...v0.11.0)\).*

- Add link filter. [\#201](https://github.com/liip/LiipImagineBundle/pull/201) *([EmmanuelVella](https://github.com/EmmanuelVella))*
- Thumbnail filter was not applied when allow\_upscale=true and one dimension. [\#200](https://github.com/liip/LiipImagineBundle/pull/200) *([teohhanhui](https://github.com/teohhanhui))*
- Add badge poser in README. [\#199](https://github.com/liip/LiipImagineBundle/pull/199) *([agiuliano](https://github.com/agiuliano))*
- Add docs about allow\_scale of thumbnail filter. [\#198](https://github.com/liip/LiipImagineBundle/pull/198) *([havvg](https://github.com/havvg))*
- Add documentation on S3 object URL options. [\#197](https://github.com/liip/LiipImagineBundle/pull/197) *([havvg](https://github.com/havvg))*


## [v0.10.1](https://github.com/liip/LiipImagineBundle/tree/v0.10.1)

*Released on* 2013-05-29 *and assigned* [`0.10.1`](https://github.com/liip/LiipImagineBundle/releases/tag/v0.10.1) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/v0.10.0...v0.10.1)\).*

- Mkdir\(\) doesn't take care about the umask. [\#189](https://github.com/liip/LiipImagineBundle/pull/189) *([KingCrunch](https://github.com/KingCrunch))*
- The quickest PR to review I guess. [\#188](https://github.com/liip/LiipImagineBundle/pull/188) *([Sydney-o9](https://github.com/Sydney-o9))*


## [v0.10.0](https://github.com/liip/LiipImagineBundle/tree/v0.10.0)

*Released on* 2013-05-17 *and assigned* [`0.10.0`](https://github.com/liip/LiipImagineBundle/releases/tag/v0.10.0) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/v0.9.4...v0.10.0)\).*

- CacheResolver. [\#184](https://github.com/liip/LiipImagineBundle/pull/184) *([havvg](https://github.com/havvg))*
- Fix broken tests on windows. [\#179](https://github.com/liip/LiipImagineBundle/pull/179) *([kevinarcher](https://github.com/kevinarcher))*


## [v0.9.4](https://github.com/liip/LiipImagineBundle/tree/v0.9.4)

*Released on* 2013-05-14 *and assigned* [`0.9.4`](https://github.com/liip/LiipImagineBundle/releases/tag/v0.9.4) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/v0.9.3...v0.9.4)\).*

- Fix doc of CacheManager::resolve to not lie. [\#186](https://github.com/liip/LiipImagineBundle/pull/186) *([dbu](https://github.com/dbu))*
- Small documentation fix for getting browserPath for a thumb from controller. [\#178](https://github.com/liip/LiipImagineBundle/pull/178) *([leberknecht](https://github.com/leberknecht))*
- Improve phpcr loader doc. [\#177](https://github.com/liip/LiipImagineBundle/pull/177) *([dbu](https://github.com/dbu))*
- Allow Symfony 2.3 and greater. [\#176](https://github.com/liip/LiipImagineBundle/pull/176) *([tommygnr](https://github.com/tommygnr))*


## [v0.9.3](https://github.com/liip/LiipImagineBundle/tree/v0.9.3)

*Released on* 2013-04-17 *and assigned* [`0.9.3`](https://github.com/liip/LiipImagineBundle/releases/tag/v0.9.3) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/v0.9.2...v0.9.3)\).*

- Add CacheManagerAwareTrait. [\#173](https://github.com/liip/LiipImagineBundle/pull/173) *([havvg](https://github.com/havvg))*


## [v0.9.2](https://github.com/liip/LiipImagineBundle/tree/v0.9.2)

*Released on* 2013-04-08 *and assigned* [`0.9.2`](https://github.com/liip/LiipImagineBundle/releases/tag/v0.9.2) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/v0.9.1...v0.9.2)\).*

- Add background filter. [\#171](https://github.com/liip/LiipImagineBundle/pull/171) *([maxbeutel](https://github.com/maxbeutel))*
- Made the phpcr loader search for the requested path with or without a file extension. [\#169](https://github.com/liip/LiipImagineBundle/pull/169) *([lsmith77](https://github.com/lsmith77))*
- Use composer require command. [\#160](https://github.com/liip/LiipImagineBundle/pull/160) *([gimler](https://github.com/gimler))*
- Update installation.md. [\#159](https://github.com/liip/LiipImagineBundle/pull/159) *([dlondero](https://github.com/dlondero))*
- Update README.md. [\#158](https://github.com/liip/LiipImagineBundle/pull/158) *([dlondero](https://github.com/dlondero))*


## [v0.9.1](https://github.com/liip/LiipImagineBundle/tree/v0.9.1)

*Released on* 2013-02-20 *and assigned* [`0.9.1`](https://github.com/liip/LiipImagineBundle/releases/tag/v0.9.1) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/v0.9.0...v0.9.1)\).*

- Added the 'strip' filter. [\#152](https://github.com/liip/LiipImagineBundle/pull/152) *([uwej711](https://github.com/uwej711))*


## [v0.9.0](https://github.com/liip/LiipImagineBundle/tree/v0.9.0)

*Released on* 2013-02-13 *and assigned* [`0.9.0`](https://github.com/liip/LiipImagineBundle/releases/tag/v0.9.0) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/371140531ca574af759ef44b8eff5dac43e13df1...v0.9.0)\).*

- Add FilterManager::applyFilter. [\#150](https://github.com/liip/LiipImagineBundle/pull/150) *([havvg](https://github.com/havvg))*
- Add "Introduction" chapter to documentation. [\#149](https://github.com/liip/LiipImagineBundle/pull/149) *([havvg](https://github.com/havvg))*
- Split documentation and README into chapters. [\#148](https://github.com/liip/LiipImagineBundle/pull/148) *([havvg](https://github.com/havvg))*
- Add route options to routing loader. [\#138](https://github.com/liip/LiipImagineBundle/pull/138) *([sveriger](https://github.com/sveriger))*
- Added a data loader for PHPCR. [\#134](https://github.com/liip/LiipImagineBundle/pull/134) *([Burgov](https://github.com/Burgov))*
- __\[Minor\]__ Cleanup. [\#133](https://github.com/liip/LiipImagineBundle/pull/133) *([havvg](https://github.com/havvg))*
- Add image form type. [\#130](https://github.com/liip/LiipImagineBundle/pull/130) *([EmmanuelVella](https://github.com/EmmanuelVella))*
- New minor Imagine version. [\#129](https://github.com/liip/LiipImagineBundle/pull/129) *([jcrombez](https://github.com/jcrombez))*
- Pathinfo-related notices in generateUrl\(\). [\#128](https://github.com/liip/LiipImagineBundle/pull/128) *([thanosp](https://github.com/thanosp))*
- Updated the Imagine library to version 0.4.0. [\#127](https://github.com/liip/LiipImagineBundle/pull/127) *([ubick](https://github.com/ubick))*
- Added some documentation to Outside the web root chapter. [\#122](https://github.com/liip/LiipImagineBundle/pull/122) *([nass600](https://github.com/nass600))*
- Added PasteFilterLoader. [\#118](https://github.com/liip/LiipImagineBundle/pull/118) *([lmcd](https://github.com/lmcd))*
- Add info on the StreamWrapper of GaufretteBundle. [\#115](https://github.com/liip/LiipImagineBundle/pull/115) *([havvg](https://github.com/havvg))*
- Properly set config parameter in the container. [\#113](https://github.com/liip/LiipImagineBundle/pull/113) *([kevinarcher](https://github.com/kevinarcher))*
- Adding cache directory permissions configuration parameter. [\#112](https://github.com/liip/LiipImagineBundle/pull/112) *([kevinarcher](https://github.com/kevinarcher))*
- Renamed "auto\_clear\_cache" to "cache\_clearer". [\#102](https://github.com/liip/LiipImagineBundle/pull/102) *([Spea](https://github.com/Spea))*
- Added option to disable cache\_clearer. [\#101](https://github.com/liip/LiipImagineBundle/pull/101) *([Spea](https://github.com/Spea))*
- Cache resolver service argument order in readme. [\#100](https://github.com/liip/LiipImagineBundle/pull/100) *([johnnypeck](https://github.com/johnnypeck))*
- Added GridFS Loader. [\#99](https://github.com/liip/LiipImagineBundle/pull/99) *([jdewit](https://github.com/jdewit))*
- Update composer.json. [\#95](https://github.com/liip/LiipImagineBundle/pull/95) *([krispypen](https://github.com/krispypen))*
- Use the basePath in the file path resolver \(useful in "\_dev" or "\_\*" env\). [\#92](https://github.com/liip/LiipImagineBundle/pull/92) *([khepin](https://github.com/khepin))*
- Add basePath injection to filesystem resolver. [\#91](https://github.com/liip/LiipImagineBundle/pull/91) *([havvg](https://github.com/havvg))*
- Add "using the controller as a service" to the documentation. [\#88](https://github.com/liip/LiipImagineBundle/pull/88) *([inmarelibero](https://github.com/inmarelibero))*
- __\[Minor\]__ fix in readme. [\#87](https://github.com/liip/LiipImagineBundle/pull/87) *([stefax](https://github.com/stefax))*
- Ensure that hardcoded filter formats are applied. [\#86](https://github.com/liip/LiipImagineBundle/pull/86) *([lsmith77](https://github.com/lsmith77))*
- Cache clearer only registered for sf2.1 \(fixes [\#81](https://github.com/liip/LiipImagineBundle/issues/81)\). [\#82](https://github.com/liip/LiipImagineBundle/pull/82) *([digitalkaoz](https://github.com/digitalkaoz))*
- Issue 43 - Added a cache clearer for generated images. [\#80](https://github.com/liip/LiipImagineBundle/pull/80) *([sixty-nine](https://github.com/sixty-nine))*
- Added NoCacheResolver. [\#76](https://github.com/liip/LiipImagineBundle/pull/76) *([ghost](https://github.com/ghost))*
- Fixed errors in README.md. [\#75](https://github.com/liip/LiipImagineBundle/pull/75) *([iamdto](https://github.com/iamdto))*
- Add LoggerInterface to AmazonS3Resolver. [\#70](https://github.com/liip/LiipImagineBundle/pull/70) *([havvg](https://github.com/havvg))*
- Fix AmazonS3Resolver. [\#69](https://github.com/liip/LiipImagineBundle/pull/69) *([havvg](https://github.com/havvg))*
- Several fixes to the AmazonS3Resolver based on feedback. [\#68](https://github.com/liip/LiipImagineBundle/pull/68) *([havvg](https://github.com/havvg))*
- Move getFilePath to AbstractFilesystemResolver. [\#67](https://github.com/liip/LiipImagineBundle/pull/67) *([havvg](https://github.com/havvg))*
- Add AmazonS3Resolver and ResolverInterface::remove. [\#66](https://github.com/liip/LiipImagineBundle/pull/66) *([havvg](https://github.com/havvg))*
- Throwing an error if source image doesn't exist. [\#65](https://github.com/liip/LiipImagineBundle/pull/65) *([fixe](https://github.com/fixe))*
- Add GaufretteFilesystemLoader. [\#63](https://github.com/liip/LiipImagineBundle/pull/63) *([havvg](https://github.com/havvg))*
- Mark image services as non public. [\#62](https://github.com/liip/LiipImagineBundle/pull/62) *([lstrojny](https://github.com/lstrojny))*
- Updates PdfTransformer so that imagick is injected. [\#61](https://github.com/liip/LiipImagineBundle/pull/61) *([lucasaba](https://github.com/lucasaba))*
- Add crop filter; add missing option for thumbnail filter. [\#58](https://github.com/liip/LiipImagineBundle/pull/58) *([gimler](https://github.com/gimler))*
- Add file transformers to the file loader. [\#57](https://github.com/liip/LiipImagineBundle/pull/57) *([lucasaba](https://github.com/lucasaba))*
- Use of protected class properties in FilesystemLoader. [\#54](https://github.com/liip/LiipImagineBundle/pull/54) *([petrjaros](https://github.com/petrjaros))*
- 'cache\_resolver' property name change. [\#53](https://github.com/liip/LiipImagineBundle/pull/53) *([petrjaros](https://github.com/petrjaros))*
- Add composer.json. [\#51](https://github.com/liip/LiipImagineBundle/pull/51) *([iampersistent](https://github.com/iampersistent))*
- Fix for last version of Symfony. [\#50](https://github.com/liip/LiipImagineBundle/pull/50) *([benji07](https://github.com/benji07))*
- Allowed a file extension to be inferred for source files without one. [\#47](https://github.com/liip/LiipImagineBundle/pull/47) *([web-dev](https://github.com/web-dev))*
- Added a configuration option for the data root. [\#46](https://github.com/liip/LiipImagineBundle/pull/46) *([web-dev](https://github.com/web-dev))*
- README update: source img outside web root. [\#45](https://github.com/liip/LiipImagineBundle/pull/45) *([scoolen](https://github.com/scoolen))*
- Fixing typo in README.md. [\#44](https://github.com/liip/LiipImagineBundle/pull/44) *([stefanosala](https://github.com/stefanosala))*
- Update template extension and helper names. [\#41](https://github.com/liip/LiipImagineBundle/pull/41) *([iampersistent](https://github.com/iampersistent))*
- Refactor RelativeResize code and add documentation. [\#39](https://github.com/liip/LiipImagineBundle/pull/39) *([jmikola](https://github.com/jmikola))*
- Add Resize and RelativeResize filters. [\#37](https://github.com/liip/LiipImagineBundle/pull/37) *([jmikola](https://github.com/jmikola))*
- Extracted the abstract class Resolver from WebPathResolver. [\#35](https://github.com/liip/LiipImagineBundle/pull/35) *([sixty-nine](https://github.com/sixty-nine))*
- Fix service name. [\#34](https://github.com/liip/LiipImagineBundle/pull/34) *([lenar](https://github.com/lenar))*
- Removed webRoot logic outside controller. [\#28](https://github.com/liip/LiipImagineBundle/pull/28) *([LouTerrailloune](https://github.com/LouTerrailloune))*
- Fixed redirect using wrong variable. [\#27](https://github.com/liip/LiipImagineBundle/pull/27) *([Spea](https://github.com/Spea))*
- Tweak response creation. [\#26](https://github.com/liip/LiipImagineBundle/pull/26) *([lsmith77](https://github.com/lsmith77))*
- Fixed unit tests, fixes GH-22. [\#24](https://github.com/liip/LiipImagineBundle/pull/24) *([ghost](https://github.com/ghost))*
- Added missing docblock. [\#20](https://github.com/liip/LiipImagineBundle/pull/20) *([LouTerrailloune](https://github.com/LouTerrailloune))*
- Allow-all default setting for liip\_imagine.formats. [\#14](https://github.com/liip/LiipImagineBundle/pull/14) *([ghost](https://github.com/ghost))*
- Added support for many filter transformations in one filter set \(style\), fixes GH-1. [\#11](https://github.com/liip/LiipImagineBundle/pull/11) *([ghost](https://github.com/ghost))*
- Fixed ImagineLoader - Cache prefix was not used in urls. [\#6](https://github.com/liip/LiipImagineBundle/pull/6) *([ghost](https://github.com/ghost))*
- Fixed CachePathResolver\#getBrowserPath. [\#5](https://github.com/liip/LiipImagineBundle/pull/5) *([ghost](https://github.com/ghost))*
- Added check for the existence of extension info. [\#147](https://github.com/liip/LiipImagineBundle/pull/147) *([thanosp](https://github.com/thanosp))*
- Add Tests for bundle features. [\#140](https://github.com/liip/LiipImagineBundle/pull/140) *([havvg](https://github.com/havvg))*

---

*The templates for new release changelog entries are created using
[github_changelog_generator](https://github.com/skywinder/Github-Changelog-Generator); the final formatting and edits are
completed manually by one of the many [project contributors](https://github.com/liip/LiipImagineBundle/graphs/contributors).*
