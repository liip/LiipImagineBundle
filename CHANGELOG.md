# Change Log

## [Unreleased](https://github.com/liip/LiipImagineBundle/tree/HEAD) (2017-xx-xx)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.7.4...HEAD)

## [1.7.3](https://github.com/liip/LiipImagineBundle/tree/1.7.3) (2017-03-01)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.7.3...1.7.4)

- \[Bug\] Revert adding leading slash to S3 class names [\#893](https://github.com/liip/LiipImagineBundle/pull/893) ([cedricziel](https://github.com/cedricziel))

## [1.7.3](https://github.com/liip/LiipImagineBundle/tree/1.7.3) (2017-03-01)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.7.2...1.7.3)

- \[Tests\] Support PHPUnit 5.x (and remove depredations) [\#887](https://github.com/liip/LiipImagineBundle/pull/887) ([robfrawley](https://github.com/robfrawley))
- \[Tests\] Assert expected deprecation using symfony/phpunit-bridge [\#886](https://github.com/liip/LiipImagineBundle/pull/886) ([robfrawley](https://github.com/robfrawley))
- \[Minor\] \[Docs\] Fix typo in general filters documentation [\#888](https://github.com/liip/LiipImagineBundle/pull/888) ([svenluijten](https://github.com/svenluijten))
- \[Loader\] Add bundle resources to safe path when requested [\#883](https://github.com/liip/LiipImagineBundle/pull/883) ([bobvandevijver](https://github.com/bobvandevijver), [robfrawley](https://github.com/robfrawley))
- \[Tests\] Enable mongo unit tests on PHP7 using "mongo" => "mongodb" extension adapter [\#882](https://github.com/liip/LiipImagineBundle/pull/882) ([robfrawley](https://github.com/robfrawley))
- \[Loader\] \[Locator\] FileSystemLocator service must not be shared [\#875](https://github.com/liip/LiipImagineBundle/pull/875) ([robfrawley](https://github.com/liip/LiipImagineBundle/pull/875))

## [1.7.2](https://github.com/liip/LiipImagineBundle/tree/1.7.2) (2017-02-07)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.7.1...1.7.2)

- \[Loader\] Abstract filesystem resource locator and legacy insecure locator implementation [\#866](https://github.com/liip/LiipImagineBundle/pull/866) ([robfrawley](https://github.com/robfrawley))
- \[Minor\] \[Loader\] Fix for FileSystemLoader annotation [\#868](https://github.com/liip/LiipImagineBundle/pull/868) ([tgabi333](https://github.com/tgabi333))
- \[DependencyInjection\] Container logging for compiler passes [\#867](https://github.com/liip/LiipImagineBundle/pull/867) ([robfrawley](https://github.com/robfrawley))
- \[CI\] Use Prestissimo package for Travis build [\#864](https://github.com/liip/LiipImagineBundle/pull/864) ([robfrawley](https://github.com/robfrawley))
- \[GitHub\] Add Hithub templates for issues and PRs [\#863](https://github.com/liip/LiipImagineBundle/pull/863) ([robfrawley](https://github.com/robfrawley))
- \[Symfony\] Bug fixes and deprecation cleanup for Symfony 3.3 [\#860](https://github.com/liip/LiipImagineBundle/pull/860) ([robfrawley](https://github.com/robfrawley))
- \[Filter\] Upscale filter should use the highest dimension to calculate ratio [\#856](https://github.com/liip/LiipImagineBundle/pull/856) ([Rattler3](https://github.com/Rattler3))

## [1.7.1](https://github.com/liip/LiipImagineBundle/tree/1.7.1) (2017-01-19)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.7.0...1.7.1)

- Allow multiple root paths for FileSystemLoader [\#851](https://github.com/liip/LiipImagineBundle/pull/851) ([robfrawley](https://github.com/robfrawley))
- Fix strange wording in readme [\#847](https://github.com/liip/LiipImagineBundle/pull/847) ([svenluijten](https://github.com/svenluijten))

## [1.7.0](https://github.com/liip/LiipImagineBundle/tree/1.7.0) (2017-01-08)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.6.0...1.7.0)

- Set DefaultMetadataReader when ext-exif is not present [\#841](https://github.com/liip/LiipImagineBundle/pull/841) ([cedricziel](https://github.com/cedricziel))
- Updating twig call to utilise asset\(\), to match README.md \(closes \#830\) [\#836](https://github.com/liip/LiipImagineBundle/pull/836) ([antoligy](https://github.com/antoligy))
- Exclude "Tests" directory from classmap [\#835](https://github.com/liip/LiipImagineBundle/pull/835) ([pamil](https://github.com/pamil))
- Require components that no longer ship with Symfony FrameworkBundle 3.2 [\#832](https://github.com/liip/LiipImagineBundle/pull/832) ([rpkamp](https://github.com/rpkamp))
- Document how web paths are built [\#829](https://github.com/liip/LiipImagineBundle/pull/829) ([greg0ire](https://github.com/greg0ire))
- Wrap relative path with asset\(\) Twig function [\#825](https://github.com/liip/LiipImagineBundle/pull/825) ([bocharsky-bw](https://github.com/bocharsky-bw))
- Update data-loaders.rst [\#821](https://github.com/liip/LiipImagineBundle/pull/821) ([IllesAprod](https://github.com/IllesAprod))
- Updating 2.0 with corrections from 1.0 [\#820](https://github.com/liip/LiipImagineBundle/pull/820) ([antoligy](https://github.com/antoligy))
- Typo fix [\#819](https://github.com/liip/LiipImagineBundle/pull/819) ([redjanym](https://github.com/redjanym))
- Fix RST indentation error in AWS S3 cache resolver documentation [\#809](https://github.com/liip/LiipImagineBundle/pull/809) ([GeoffreyHervet](https://github.com/GeoffreyHervet))
- Update basic-usage.rst [\#805](https://github.com/liip/LiipImagineBundle/pull/805) ([you-ser](https://github.com/you-ser))
- Add data\_loader config to doc [\#803](https://github.com/liip/LiipImagineBundle/pull/803) ([davidfuhr](https://github.com/davidfuhr))
- RST Typo Fix and Clarification for Watermark Docs [\#802](https://github.com/liip/LiipImagineBundle/pull/802) ([robfrawley](https://github.com/robfrawley))
- Update Source to Newly Merged Style Rule Additions [\#800](https://github.com/liip/LiipImagineBundle/pull/800) ([robfrawley](https://github.com/robfrawley))
- Bugfix: Remove Short Array Syntax and Fix \(Minor\) Recent Merge Issues [\#799](https://github.com/liip/LiipImagineBundle/pull/799) ([robfrawley](https://github.com/robfrawley))
- Add Symfony Framework 3.1.x and 3.2-dev to build matrix [\#796](https://github.com/liip/LiipImagineBundle/pull/796) ([cedricziel](https://github.com/cedricziel))
- Add visibility argument to service definition [\#795](https://github.com/liip/LiipImagineBundle/pull/795) ([cedricziel](https://github.com/cedricziel))
- Bugfix for Failing Tests Introduced in \#777 [\#793](https://github.com/liip/LiipImagineBundle/pull/793) ([robfrawley](https://github.com/robfrawley))
- Added php\_cs.dist / Updated .styleci.yml / Fixed and updated .travis.yml [\#792](https://github.com/liip/LiipImagineBundle/pull/792) ([robfrawley](https://github.com/robfrawley))
- Add LICENSE.md [\#790](https://github.com/liip/LiipImagineBundle/pull/790) ([robfrawley](https://github.com/robfrawley))
- Updated README.md and RST Documentation [\#789](https://github.com/liip/LiipImagineBundle/pull/789) ([robfrawley](https://github.com/robfrawley))
- Updated CHANGELOG.md [\#788](https://github.com/liip/LiipImagineBundle/pull/788) ([robfrawley](https://github.com/robfrawley))
- List deprecation - closes \#731 [\#787](https://github.com/liip/LiipImagineBundle/pull/787) ([antoligy](https://github.com/antoligy))
- Cleanup FileSystemLoader \(Followup to \#775\) [\#785](https://github.com/liip/LiipImagineBundle/pull/785) ([robfrawley](https://github.com/robfrawley))
- Tempdir for postprocessors [\#779](https://github.com/liip/LiipImagineBundle/pull/779) ([jehaby](https://github.com/jehaby))
- Add visibility argument to flysystem resolver [\#777](https://github.com/liip/LiipImagineBundle/pull/777) ([cedricziel](https://github.com/cedricziel))
- Amend path resolution handlers and outside root check conditional in FileSystemLoader [\#775](https://github.com/liip/LiipImagineBundle/pull/775) ([robfrawley](https://github.com/robfrawley))
- Scale filter and Downscale and Upscale as derivatives, with a new feature [\#773](https://github.com/liip/LiipImagineBundle/pull/773) ([deviprsd21](https://github.com/deviprsd21))
- Applied fixes from StyleCI [\#768](https://github.com/liip/LiipImagineBundle/pull/768) ([lsmith77](https://github.com/lsmith77))
- Replaced deprecated factory\_class and factory\_method [\#767](https://github.com/liip/LiipImagineBundle/pull/767) ([rvanlaarhoven](https://github.com/rvanlaarhoven))
- Update basic-usage.rst [\#766](https://github.com/liip/LiipImagineBundle/pull/766) ([nochecksum](https://github.com/nochecksum))
- Implemented ConfigurablePostProcessorInterface in OptiPngPostProcessor [\#764](https://github.com/liip/LiipImagineBundle/pull/764) ([jehaby](https://github.com/jehaby))

## [1.6.0](https://github.com/liip/LiipImagineBundle/tree/1.6.0) (2016-07-22)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.5.3...1.6.0)

- Input is added twice in the OptiPngProcessor. [\#762](https://github.com/liip/LiipImagineBundle/pull/762) ([antoligy](https://github.com/antoligy))
- Enable configuration of post processors using parameters \(closes \#720\) [\#759](https://github.com/liip/LiipImagineBundle/pull/759) ([antoligy](https://github.com/antoligy))
- Applied fixes from StyleCI [\#758](https://github.com/liip/LiipImagineBundle/pull/758) ([lsmith77](https://github.com/lsmith77))
- Applied fixes from StyleCI [\#757](https://github.com/liip/LiipImagineBundle/pull/757) ([lsmith77](https://github.com/lsmith77))
- Add configuration options for jpegoptim post-processor [\#756](https://github.com/liip/LiipImagineBundle/pull/756) ([dylanschoenmakers](https://github.com/dylanschoenmakers))
- Ignore invalid exif orientations [\#751](https://github.com/liip/LiipImagineBundle/pull/751) ([lstrojny](https://github.com/lstrojny))
- Quote strings starting '%' in YAML [\#745](https://github.com/liip/LiipImagineBundle/pull/745) ([jaikdean](https://github.com/jaikdean))
- Fix tempnam usages [\#723](https://github.com/liip/LiipImagineBundle/pull/723) ([1ed](https://github.com/1ed))
- background filter: allow image positioning [\#721](https://github.com/liip/LiipImagineBundle/pull/721) ([uvoelkel](https://github.com/uvoelkel))
- Add Flysystem resolver [\#715](https://github.com/liip/LiipImagineBundle/pull/715) ([cedricziel](https://github.com/cedricziel))
- Downscale filter scales an image to fit bounding box [\#696](https://github.com/liip/LiipImagineBundle/pull/696) ([aminin](https://github.com/aminin))
- Implement Imagine Grayscale filter [\#638](https://github.com/liip/LiipImagineBundle/pull/638) ([gregumo](https://github.com/gregumo))

## [1.5.3](https://github.com/liip/LiipImagineBundle/tree/1.5.3) (2016-05-06)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.5.2...1.5.3)

- add @Event annotation to let IDEs known event names and class instance [\#732](https://github.com/liip/LiipImagineBundle/pull/732) ([Haehnchen](https://github.com/Haehnchen))
- Introduce mozjpeg and pngquant post-processors, add transform options. [\#717](https://github.com/liip/LiipImagineBundle/pull/717) ([antoligy](https://github.com/antoligy))
- StreamLoader-exception-arguments [\#714](https://github.com/liip/LiipImagineBundle/pull/714) ([antonsmolin](https://github.com/antonsmolin))

## [1.5.2](https://github.com/liip/LiipImagineBundle/tree/1.5.2) (2016-02-16)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.5.1...1.5.2)

- Revert "Merge pull request \#699 from jockri/fix-background-filter" [\#709](https://github.com/liip/LiipImagineBundle/pull/709) ([mangelsnc](https://github.com/mangelsnc))

## [1.5.1](https://github.com/liip/LiipImagineBundle/tree/1.5.1) (2016-02-13)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.5.0...1.5.1)

- Fix regression introduced in bb8e4109672902e37931e0a491ff55ebac93d8e9 [\#707](https://github.com/liip/LiipImagineBundle/pull/707) ([Seldaek](https://github.com/Seldaek))

## [1.5.0](https://github.com/liip/LiipImagineBundle/tree/1.5.0) (2016-02-12)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.4.3...1.5.0)

- Applied fixes from StyleCI [\#706](https://github.com/liip/LiipImagineBundle/pull/706) ([lsmith77](https://github.com/lsmith77))
- Add FileBinary\[Interface\] to support large files without loading them in memory unnecessarily [\#705](https://github.com/liip/LiipImagineBundle/pull/705) ([Seldaek](https://github.com/Seldaek))
- Fix background filter [\#699](https://github.com/liip/LiipImagineBundle/pull/699) ([jockri](https://github.com/jockri))
- Fix undeclared variable [\#697](https://github.com/liip/LiipImagineBundle/pull/697) ([tifabien](https://github.com/tifabien))
- Update WebPathResolver.php [\#695](https://github.com/liip/LiipImagineBundle/pull/695) ([gonzalovilaseca](https://github.com/gonzalovilaseca))
- Add missing link to the filters doc [\#694](https://github.com/liip/LiipImagineBundle/pull/694) ([bocharsky-bw](https://github.com/bocharsky-bw))
- Adding optipng post transformer [\#692](https://github.com/liip/LiipImagineBundle/pull/692) ([gouaille](https://github.com/gouaille))

## [1.4.3](https://github.com/liip/LiipImagineBundle/tree/1.4.3) (2016-01-14)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.4.2...1.4.3)

- Fixed build issues [\#691](https://github.com/liip/LiipImagineBundle/pull/691) ([yceruto](https://github.com/yceruto))
- Fixed doc errors reported by docs build tool [\#690](https://github.com/liip/LiipImagineBundle/pull/690) ([javiereguiluz](https://github.com/javiereguiluz))
- Explicit attr definition was added [\#688](https://github.com/liip/LiipImagineBundle/pull/688) ([ostretsov](https://github.com/ostretsov))
- Flysystem support added. [\#674](https://github.com/liip/LiipImagineBundle/pull/674) ([graundas](https://github.com/graundas))

## [1.4.2](https://github.com/liip/LiipImagineBundle/tree/1.4.2) (2015-12-29)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.4.1...1.4.2)

- Proxy resolver allow find and replace and regexp strategies [\#687](https://github.com/liip/LiipImagineBundle/pull/687) ([makasim](https://github.com/makasim))
- added contributing docs [\#681](https://github.com/liip/LiipImagineBundle/pull/681) ([helios-ag](https://github.com/helios-ag))
- rebased commands document patch, see \#533 [\#680](https://github.com/liip/LiipImagineBundle/pull/680) ([helios-ag](https://github.com/helios-ag))

## [1.4.1](https://github.com/liip/LiipImagineBundle/tree/1.4.1) (2015-12-27)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.4.0...1.4.1)

- Aws sdk v3 [\#685](https://github.com/liip/LiipImagineBundle/pull/685) ([makasim](https://github.com/makasim))

## [1.4.0](https://github.com/liip/LiipImagineBundle/tree/1.4.0) (2015-12-27)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.3.3...1.4.0)

- \[resolver\] Add ability to force resolver. [\#684](https://github.com/liip/LiipImagineBundle/pull/684) ([makasim](https://github.com/makasim))

## [1.3.3](https://github.com/liip/LiipImagineBundle/tree/1.3.3) (2015-12-27)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.3.2...1.3.3)

- Destruct image to cleanup memory [\#682](https://github.com/liip/LiipImagineBundle/pull/682) ([cmodijk](https://github.com/cmodijk))

## [1.3.2](https://github.com/liip/LiipImagineBundle/tree/1.3.2) (2015-12-10)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.3.1...1.3.2)

- Removed UrlGenerator deprecations from symfony 2.8 [\#673](https://github.com/liip/LiipImagineBundle/pull/673) ([sebastianblum](https://github.com/sebastianblum))
- Typo [\#668](https://github.com/liip/LiipImagineBundle/pull/668) ([benoitMariaux](https://github.com/benoitMariaux))
- Misc. fixes and improvements to the docs [\#667](https://github.com/liip/LiipImagineBundle/pull/667) ([javiereguiluz](https://github.com/javiereguiluz))
- skip MongoDB ODM related tests on PHP7 and HHVM [\#659](https://github.com/liip/LiipImagineBundle/pull/659) ([lsmith77](https://github.com/lsmith77))
- Fix all test fails in master \(just to check\) [\#658](https://github.com/liip/LiipImagineBundle/pull/658) ([kamazee](https://github.com/kamazee))
- Fix handling invalid orientation in AutoRotateFilterLoader & test exceptions [\#657](https://github.com/liip/LiipImagineBundle/pull/657) ([kamazee](https://github.com/kamazee))
- Fix broken CacheResolver tests \(\#650\) [\#655](https://github.com/liip/LiipImagineBundle/pull/655) ([kamazee](https://github.com/kamazee))
- - Task: correctly handles all rotations, even those involving flippin… [\#654](https://github.com/liip/LiipImagineBundle/pull/654) ([Heshyo](https://github.com/Heshyo))
- Incorporate feedback from @WouterJ for PR 651 [\#653](https://github.com/liip/LiipImagineBundle/pull/653) ([kix](https://github.com/kix))
- Applied fixes from StyleCI [\#652](https://github.com/liip/LiipImagineBundle/pull/652) ([lsmith77](https://github.com/lsmith77))
- Add notes on basic usage [\#651](https://github.com/liip/LiipImagineBundle/pull/651) ([kix](https://github.com/kix))
- Fix travis php version [\#649](https://github.com/liip/LiipImagineBundle/pull/649) ([Koc](https://github.com/Koc))
- Update StreamLoader.php [\#648](https://github.com/liip/LiipImagineBundle/pull/648) ([kix](https://github.com/kix))
- Applied fixes from StyleCI [\#646](https://github.com/liip/LiipImagineBundle/pull/646) ([lsmith77](https://github.com/lsmith77))
- updated build matrix [\#645](https://github.com/liip/LiipImagineBundle/pull/645) ([lsmith77](https://github.com/lsmith77))
- Fix typo [\#634](https://github.com/liip/LiipImagineBundle/pull/634) ([trsteel88](https://github.com/trsteel88))
- Added support for special characters and white spaces in image name [\#629](https://github.com/liip/LiipImagineBundle/pull/629) ([ivanbarlog](https://github.com/ivanbarlog))
- Updated docs for features introduced in Symfony 2.4 [\#621](https://github.com/liip/LiipImagineBundle/pull/621) ([foaly-nr1](https://github.com/foaly-nr1))
- Use identity instead equality [\#619](https://github.com/liip/LiipImagineBundle/pull/619) ([piotrantosik](https://github.com/piotrantosik))
- context parameter cannot be an empty string [\#618](https://github.com/liip/LiipImagineBundle/pull/618) ([aistis-](https://github.com/aistis-))
- introduced DownscaleFilterLoader [\#610](https://github.com/liip/LiipImagineBundle/pull/610) ([sascha-meissner](https://github.com/sascha-meissner))

## [1.3.1](https://github.com/liip/LiipImagineBundle/tree/1.3.1) (2015-08-27)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.3.0...1.3.1)

- Fix deprecated twig filter syntax [\#631](https://github.com/liip/LiipImagineBundle/pull/631) ([Rattler3](https://github.com/Rattler3))
- fix invalid yaml [\#623](https://github.com/liip/LiipImagineBundle/pull/623) ([carlcraig](https://github.com/carlcraig))
- switch to docker based travis infrastructure [\#622](https://github.com/liip/LiipImagineBundle/pull/622) ([lsmith77](https://github.com/lsmith77))
- Return string, not Twig\_Markup object in Twig extension [\#615](https://github.com/liip/LiipImagineBundle/pull/615) ([lstrojny](https://github.com/lstrojny))
- Use is\_file\(\) instead of Filesystem::exists\(\) [\#614](https://github.com/liip/LiipImagineBundle/pull/614) ([lstrojny](https://github.com/lstrojny))
- Make it easier to get a dev environment up and running [\#613](https://github.com/liip/LiipImagineBundle/pull/613) ([lstrojny](https://github.com/lstrojny))
- Fix code block into README [\#608](https://github.com/liip/LiipImagineBundle/pull/608) ([PedroTroller](https://github.com/PedroTroller))
- fix upscale size not being calculated correctly [\#561](https://github.com/liip/LiipImagineBundle/pull/561) ([scuben](https://github.com/scuben))

## [1.3.0](https://github.com/liip/LiipImagineBundle/tree/1.3.0) (2015-06-04)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.2.7...1.3.0)

- use setFactory service definition method for symfony \>= 2.6 \(when possible\) [\#566](https://github.com/liip/LiipImagineBundle/pull/566) ([adam187](https://github.com/adam187))

## [1.2.7](https://github.com/liip/LiipImagineBundle/tree/1.2.7) (2015-06-02)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.2.6...1.2.7)

- Make AwsS3Resolver compatible with SDK v3 [\#605](https://github.com/liip/LiipImagineBundle/pull/605) ([cdaguerre](https://github.com/cdaguerre))
- \[Doc\] Add missing coma and fix indentation in README.md [\#604](https://github.com/liip/LiipImagineBundle/pull/604) ([grena](https://github.com/grena))
- Removed TransformerInterface [\#603](https://github.com/liip/LiipImagineBundle/pull/603) ([rvanlaarhoven](https://github.com/rvanlaarhoven))
- remove duplicate parameter [\#601](https://github.com/liip/LiipImagineBundle/pull/601) ([ip512](https://github.com/ip512))
- Fix typo [\#600](https://github.com/liip/LiipImagineBundle/pull/600) ([hpatoio](https://github.com/hpatoio))
- Adding details to use the bundle with remote images [\#569](https://github.com/liip/LiipImagineBundle/pull/569) ([flug](https://github.com/flug))

## [1.2.6](https://github.com/liip/LiipImagineBundle/tree/1.2.6) (2015-04-24)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.2.5...1.2.6)

- Check $filters is an array [\#596](https://github.com/liip/LiipImagineBundle/pull/596) ([trsteel88](https://github.com/trsteel88))

## [1.2.5](https://github.com/liip/LiipImagineBundle/tree/1.2.5) (2015-04-08)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.2.4...1.2.5)

- Add image rotate filter [\#588](https://github.com/liip/LiipImagineBundle/pull/588) ([bocharsky-bw](https://github.com/bocharsky-bw))
- run php-cs-fixer on bundle [\#583](https://github.com/liip/LiipImagineBundle/pull/583) ([trsteel88](https://github.com/trsteel88))
- Fix typo [\#582](https://github.com/liip/LiipImagineBundle/pull/582) ([bicpi](https://github.com/bicpi))
- Fix typos [\#581](https://github.com/liip/LiipImagineBundle/pull/581) ([bicpi](https://github.com/bicpi))
- Fix typos [\#580](https://github.com/liip/LiipImagineBundle/pull/580) ([bicpi](https://github.com/bicpi))

## [1.2.4](https://github.com/liip/LiipImagineBundle/tree/1.2.4) (2015-03-27)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.2.3...1.2.4)

- Update how missing filters are logged [\#579](https://github.com/liip/LiipImagineBundle/pull/579) ([trsteel88](https://github.com/trsteel88))
- use isDefined method for OptionsResolver instead of isKnown  \(when possible\) [\#567](https://github.com/liip/LiipImagineBundle/pull/567) ([adam187](https://github.com/adam187))

## [1.2.3](https://github.com/liip/LiipImagineBundle/tree/1.2.3) (2015-02-22)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.2.2...1.2.3)

- fix invalid in\_array [\#565](https://github.com/liip/LiipImagineBundle/pull/565) ([digitalkaoz](https://github.com/digitalkaoz))
- Add a short introductory paragraph about the bundle [\#559](https://github.com/liip/LiipImagineBundle/pull/559) ([javiereguiluz](https://github.com/javiereguiluz))
- Update Filters.rst [\#556](https://github.com/liip/LiipImagineBundle/pull/556) ([Spawnrad](https://github.com/Spawnrad))
- Fixed the syntax of the internal doc links [\#554](https://github.com/liip/LiipImagineBundle/pull/554) ([javiereguiluz](https://github.com/javiereguiluz))
- Updated README.md to point to new .rst doc files [\#551](https://github.com/liip/LiipImagineBundle/pull/551) ([Khez](https://github.com/Khez))
- fix typo on readme file [\#550](https://github.com/liip/LiipImagineBundle/pull/550) ([erivello](https://github.com/erivello))
- Switched the documentation from Markdown to ReStructuredText [\#545](https://github.com/liip/LiipImagineBundle/pull/545) ([javiereguiluz](https://github.com/javiereguiluz))
- Fix Filter Documentation [\#544](https://github.com/liip/LiipImagineBundle/pull/544) ([wodka](https://github.com/wodka))
- Add support for the new quality options [\#473](https://github.com/liip/LiipImagineBundle/pull/473) ([patrickli](https://github.com/patrickli))

## [1.2.2](https://github.com/liip/LiipImagineBundle/tree/1.2.2) (2015-01-08)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.2.1...1.2.2)

- Update the filter\_sets Documentation about removed configurations [\#543](https://github.com/liip/LiipImagineBundle/pull/543) ([mbiagetti](https://github.com/mbiagetti))
- implement interlace filter [\#503](https://github.com/liip/LiipImagineBundle/pull/503) ([wodka](https://github.com/wodka))

## [1.2.1](https://github.com/liip/LiipImagineBundle/tree/1.2.1) (2014-12-10)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.2.0...1.2.1)

- argument to s3 resolver prototype definition has been added [\#536](https://github.com/liip/LiipImagineBundle/pull/536) ([ruslan-polutsygan](https://github.com/ruslan-polutsygan))

## [1.2.0](https://github.com/liip/LiipImagineBundle/tree/1.2.0) (2014-12-10)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.1.1...1.2.0)

- S3 resolver put options [\#535](https://github.com/liip/LiipImagineBundle/pull/535) ([ruslan-polutsygan](https://github.com/ruslan-polutsygan))
- Fixed minor PHPDoc [\#528](https://github.com/liip/LiipImagineBundle/pull/528) ([sdaoudi](https://github.com/sdaoudi))

## [1.1.1](https://github.com/liip/LiipImagineBundle/tree/1.1.1) (2014-11-12)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.1.0...1.1.1)

- Fix crash when no post processor is defined [\#526](https://github.com/liip/LiipImagineBundle/pull/526) ([lolautruche](https://github.com/lolautruche))
- WebPathResolver - sanitize URL to directory name [\#480](https://github.com/liip/LiipImagineBundle/pull/480) ([teohhanhui](https://github.com/teohhanhui))

## [1.1.0](https://github.com/liip/LiipImagineBundle/tree/1.1.0) (2014-10-29)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.8...1.1.0)

- Post-processors - handlers to be applied on filtered image binary [\#519](https://github.com/liip/LiipImagineBundle/pull/519) ([kostiklv](https://github.com/kostiklv))

## [1.0.8](https://github.com/liip/LiipImagineBundle/tree/1.0.8) (2014-10-22)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.7...1.0.8)

- Delete АГГЗ.jpeg [\#515](https://github.com/liip/LiipImagineBundle/pull/515) ([crash21](https://github.com/crash21))
- Update configuration.md [\#513](https://github.com/liip/LiipImagineBundle/pull/513) ([hugohenrique](https://github.com/hugohenrique))

## [1.0.7](https://github.com/liip/LiipImagineBundle/tree/1.0.7) (2014-10-18)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.6...1.0.7)

- fix tests, upgrade phpunit up to 4.3 [\#511](https://github.com/liip/LiipImagineBundle/pull/511) ([makasim](https://github.com/makasim))
- Image default when notloadable exception [\#510](https://github.com/liip/LiipImagineBundle/pull/510) ([Neime](https://github.com/Neime))
- Explain how to change the default resolver [\#508](https://github.com/liip/LiipImagineBundle/pull/508) ([dbu](https://github.com/dbu))
- Updated DI configuration to the current implementation of the loader [\#500](https://github.com/liip/LiipImagineBundle/pull/500) ([peterrehm](https://github.com/peterrehm))
- Support custom output format for each filter set [\#477](https://github.com/liip/LiipImagineBundle/pull/477) ([teohhanhui](https://github.com/teohhanhui))

## [1.0.6](https://github.com/liip/LiipImagineBundle/tree/1.0.6) (2014-09-17)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.5...1.0.6)

- Fix GridFSLoader [\#461](https://github.com/liip/LiipImagineBundle/pull/461) ([aldeck](https://github.com/aldeck))

## [1.0.5](https://github.com/liip/LiipImagineBundle/tree/1.0.5) (2014-09-15)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.4...1.0.5)

- check if runtimeconfig path is stored [\#498](https://github.com/liip/LiipImagineBundle/pull/498) ([trsteel88](https://github.com/trsteel88))
- Update README.md [\#490](https://github.com/liip/LiipImagineBundle/pull/490) ([JellyBellyDev](https://github.com/JellyBellyDev))
- Update README.md [\#488](https://github.com/liip/LiipImagineBundle/pull/488) ([JellyBellyDev](https://github.com/JellyBellyDev))
- fix auto rotate [\#476](https://github.com/liip/LiipImagineBundle/pull/476) ([scuben](https://github.com/scuben))
- support animated gif [\#466](https://github.com/liip/LiipImagineBundle/pull/466) ([scuben](https://github.com/scuben))

## [1.0.4](https://github.com/liip/LiipImagineBundle/tree/1.0.4) (2014-07-30)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.3...1.0.4)

- Update WebPathResolverFactory.php [\#467](https://github.com/liip/LiipImagineBundle/pull/467) ([JJK801](https://github.com/JJK801))

## [1.0.3](https://github.com/liip/LiipImagineBundle/tree/1.0.3) (2014-07-30)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.2...1.0.3)

- Fixing issue with removed class Color [\#458](https://github.com/liip/LiipImagineBundle/pull/458) ([lstrojny](https://github.com/lstrojny))
- Added PHP 5.6 and HHVM to travis.yml [\#454](https://github.com/liip/LiipImagineBundle/pull/454) ([Nyholm](https://github.com/Nyholm))
- make the Bundle compatible with config:dump-reference command [\#452](https://github.com/liip/LiipImagineBundle/pull/452) ([lsmith77](https://github.com/lsmith77))

## [1.0.2](https://github.com/liip/LiipImagineBundle/tree/1.0.2) (2014-06-24)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.1...1.0.2)

- Update README.md [\#447](https://github.com/liip/LiipImagineBundle/pull/447) ([sgaze](https://github.com/sgaze))
- Update configuration.md [\#446](https://github.com/liip/LiipImagineBundle/pull/446) ([sgaze](https://github.com/sgaze))

## [1.0.1](https://github.com/liip/LiipImagineBundle/tree/1.0.1) (2014-06-06)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.0...1.0.1)

- \[stream\] throws exception when content cannot be read. [\#444](https://github.com/liip/LiipImagineBundle/pull/444) ([makasim](https://github.com/makasim))
- remove unused use-statement and fix phpdoc [\#441](https://github.com/liip/LiipImagineBundle/pull/441) ([UFOMelkor](https://github.com/UFOMelkor))

## [1.0.0](https://github.com/liip/LiipImagineBundle/tree/1.0.0) (2014-05-22)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.0-alpha7...1.0.0)

- added possibility to use imagine new metadata api [\#413](https://github.com/liip/LiipImagineBundle/pull/413) ([digitalkaoz](https://github.com/digitalkaoz))

## [1.0.0-alpha7](https://github.com/liip/LiipImagineBundle/tree/1.0.0-alpha7) (2014-05-22)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.0-alpha6...1.0.0-alpha7)

- Add a Signer Utility to sign filters, run php-cs-fixer on bundle [\#405](https://github.com/liip/LiipImagineBundle/pull/405) ([trsteel88](https://github.com/trsteel88))

## [1.0.0-alpha6](https://github.com/liip/LiipImagineBundle/tree/1.0.0-alpha6) (2014-05-05)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.0-alpha5...1.0.0-alpha6)

- \[router\] remove custom route loader. [\#425](https://github.com/liip/LiipImagineBundle/pull/425) ([makasim](https://github.com/makasim))

## [1.0.0-alpha5](https://github.com/liip/LiipImagineBundle/tree/1.0.0-alpha5) (2014-04-29)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.0-alpha4...1.0.0-alpha5)

- added scrutinizer config [\#420](https://github.com/liip/LiipImagineBundle/pull/420) ([digitalkaoz](https://github.com/digitalkaoz))
- Fixed testsuite \#417 in \#403 [\#419](https://github.com/liip/LiipImagineBundle/pull/419) ([ama3ing](https://github.com/ama3ing))
- increase test coverage report [\#417](https://github.com/liip/LiipImagineBundle/pull/417) ([digitalkaoz](https://github.com/digitalkaoz))
- enabled symfony 2.4 on travis [\#416](https://github.com/liip/LiipImagineBundle/pull/416) ([digitalkaoz](https://github.com/digitalkaoz))
- Update configuration.md [\#410](https://github.com/liip/LiipImagineBundle/pull/410) ([ama3ing](https://github.com/ama3ing))
- \[ci\] run tests only on 2.3 version. [\#407](https://github.com/liip/LiipImagineBundle/pull/407) ([makasim](https://github.com/makasim))
- Watermark filter documentation update. Fixes \#404 [\#406](https://github.com/liip/LiipImagineBundle/pull/406) ([ama3ing](https://github.com/ama3ing))
- Fixes \#373. Replace NotFoundHttpException with SourceNotFoundException [\#403](https://github.com/liip/LiipImagineBundle/pull/403) ([ama3ing](https://github.com/ama3ing))
- Removed unreachable statement [\#402](https://github.com/liip/LiipImagineBundle/pull/402) ([ama3ing](https://github.com/ama3ing))
- Fix of \#369 \(Trim of forwarding slash in path\) [\#401](https://github.com/liip/LiipImagineBundle/pull/401) ([ama3ing](https://github.com/ama3ing))

## [1.0.0-alpha4](https://github.com/liip/LiipImagineBundle/tree/1.0.0-alpha4) (2014-04-14)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.0-alpha3...1.0.0-alpha4)

- \[config\] correctly process resolvers\loaders section if not array or null [\#396](https://github.com/liip/LiipImagineBundle/pull/396) ([makasim](https://github.com/makasim))
- Issue\#368 wrong image path [\#395](https://github.com/liip/LiipImagineBundle/pull/395) ([serdyuka](https://github.com/serdyuka))

## [1.0.0-alpha3](https://github.com/liip/LiipImagineBundle/tree/1.0.0-alpha3) (2014-04-14)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.0-alpha2...1.0.0-alpha3)

- Added proxy to aws s3 resolver factory [\#392](https://github.com/liip/LiipImagineBundle/pull/392) ([serdyuka](https://github.com/serdyuka))

## [1.0.0-alpha2](https://github.com/liip/LiipImagineBundle/tree/1.0.0-alpha2) (2014-04-10)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/1.0.0-alpha1...1.0.0-alpha2)

- Documentation update fixes \#389 [\#390](https://github.com/liip/LiipImagineBundle/pull/390) ([ama3ing](https://github.com/ama3ing))
- \[WIP\] Added resolve events to cache manager [\#388](https://github.com/liip/LiipImagineBundle/pull/388) ([serdyuka](https://github.com/serdyuka))

## [1.0.0-alpha1](https://github.com/liip/LiipImagineBundle/tree/1.0.0-alpha1) (2014-04-07)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/v0.21.1...1.0.0-alpha1)

- Remove cli command [\#387](https://github.com/liip/LiipImagineBundle/pull/387) ([serdyuka](https://github.com/serdyuka))
- fixed and improved tests for resolve cache command [\#386](https://github.com/liip/LiipImagineBundle/pull/386) ([serdyuka](https://github.com/serdyuka))
- \[1.0\]\[config\] Fix default loader not found bug. [\#385](https://github.com/liip/LiipImagineBundle/pull/385) ([makasim](https://github.com/makasim))
- Resolve command few paths [\#383](https://github.com/liip/LiipImagineBundle/pull/383) ([serdyuka](https://github.com/serdyuka))
- Move data loaders to binary folder [\#382](https://github.com/liip/LiipImagineBundle/pull/382) ([serdyuka](https://github.com/serdyuka))
- Documentation for cli command [\#380](https://github.com/liip/LiipImagineBundle/pull/380) ([serdyuka](https://github.com/serdyuka))
- Cli command to resolve cache [\#379](https://github.com/liip/LiipImagineBundle/pull/379) ([serdyuka](https://github.com/serdyuka))
- Update README.md [\#374](https://github.com/liip/LiipImagineBundle/pull/374) ([daslicht](https://github.com/daslicht))
- \[1.0\]\[loader\] cleanup filesystem loader, simplify logic, add factory. [\#371](https://github.com/liip/LiipImagineBundle/pull/371) ([makasim](https://github.com/makasim))
- \[1.0\]\[aws-resolver\] allow configure cache\_prefix via factory. [\#370](https://github.com/liip/LiipImagineBundle/pull/370) ([makasim](https://github.com/makasim))
- \[1.0\] set web\_path resolver as default if not configured. [\#367](https://github.com/liip/LiipImagineBundle/pull/367) ([makasim](https://github.com/makasim))
- \[1.0\]\[Config\] remove path option. [\#366](https://github.com/liip/LiipImagineBundle/pull/366) ([makasim](https://github.com/makasim))
- Fixed yaml code block on stream loader documentation [\#363](https://github.com/liip/LiipImagineBundle/pull/363) ([rvanlaarhoven](https://github.com/rvanlaarhoven))
- \[1.0\]\[WebResolver\] Use baseUrl and port while generating image path. [\#362](https://github.com/liip/LiipImagineBundle/pull/362) ([makasim](https://github.com/makasim))
- Removed cache\_clearer documentation [\#359](https://github.com/liip/LiipImagineBundle/pull/359) ([rvanlaarhoven](https://github.com/rvanlaarhoven))
- CacheManager updated [\#355](https://github.com/liip/LiipImagineBundle/pull/355) ([ossinkine](https://github.com/ossinkine))
- FilesystemLoader updated [\#354](https://github.com/liip/LiipImagineBundle/pull/354) ([ossinkine](https://github.com/ossinkine))
- Update filters.md [\#346](https://github.com/liip/LiipImagineBundle/pull/346) ([zazoomauro](https://github.com/zazoomauro))

## [v0.21.1](https://github.com/liip/LiipImagineBundle/tree/v0.21.1) (2014-03-14)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/v0.21.0...v0.21.1)

## [v0.21.0](https://github.com/liip/LiipImagineBundle/tree/v0.21.0) (2014-03-14)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/v0.20.2...v0.21.0)

- Added reference on how to get image path inside a controller [\#340](https://github.com/liip/LiipImagineBundle/pull/340) ([ama3ing](https://github.com/ama3ing))
- \[1.0\] add phpunit as require-dev [\#339](https://github.com/liip/LiipImagineBundle/pull/339) ([makasim](https://github.com/makasim))
- \[1.0\] Twig helper not escape filter url [\#337](https://github.com/liip/LiipImagineBundle/pull/337) ([makasim](https://github.com/makasim))
- Added cache clearing & setting cachePrefix for Aws S3 [\#336](https://github.com/liip/LiipImagineBundle/pull/336) ([rvanlaarhoven](https://github.com/rvanlaarhoven))
- Merge latest changes in master to develop branch  [\#334](https://github.com/liip/LiipImagineBundle/pull/334) ([makasim](https://github.com/makasim))
- Update to Imagine 0.6 [\#330](https://github.com/liip/LiipImagineBundle/pull/330) ([vlastv](https://github.com/vlastv))
- \[1.0\]\[Configuration\] Cleanup bundle configuration. [\#325](https://github.com/liip/LiipImagineBundle/pull/325) ([makasim](https://github.com/makasim))
- \[1.0\]\[filter\] Dynamic filters [\#313](https://github.com/liip/LiipImagineBundle/pull/313) ([makasim](https://github.com/makasim))

## [v0.20.2](https://github.com/liip/LiipImagineBundle/tree/v0.20.2) (2014-02-20)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/v0.20.1...v0.20.2)

- GridFSLoader Bug [\#331](https://github.com/liip/LiipImagineBundle/pull/331) ([peterrehm](https://github.com/peterrehm))
- Update filters.md [\#327](https://github.com/liip/LiipImagineBundle/pull/327) ([herb123456](https://github.com/herb123456))

## [v0.20.1](https://github.com/liip/LiipImagineBundle/tree/v0.20.1) (2014-02-10)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/v0.20.0...v0.20.1)

- fixed ProxyResolver-\>getBrowserPath [\#323](https://github.com/liip/LiipImagineBundle/pull/323) ([digitalkaoz](https://github.com/digitalkaoz))

## [v0.20.0](https://github.com/liip/LiipImagineBundle/tree/v0.20.0) (2014-02-07)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/v0.19.0...v0.20.0)

- \[1.0\]\[resolver\] Decouple WebPathResolver from http request. Simplify its logic. [\#320](https://github.com/liip/LiipImagineBundle/pull/320) ([makasim](https://github.com/makasim))
- added proxy cache resolver [\#318](https://github.com/liip/LiipImagineBundle/pull/318) ([digitalkaoz](https://github.com/digitalkaoz))

## [v0.19.0](https://github.com/liip/LiipImagineBundle/tree/v0.19.0) (2014-02-07)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/v0.18.0...v0.19.0)

- improved exception on generation failure [\#321](https://github.com/liip/LiipImagineBundle/pull/321) ([digitalkaoz](https://github.com/digitalkaoz))
- added background\_image filter [\#319](https://github.com/liip/LiipImagineBundle/pull/319) ([digitalkaoz](https://github.com/digitalkaoz))
- \[1.0\] Fix tests on current develop branch [\#316](https://github.com/liip/LiipImagineBundle/pull/316) ([makasim](https://github.com/makasim))
- \[1.0\]\[cache\] CacheResolver has to cache isStored method too. [\#308](https://github.com/liip/LiipImagineBundle/pull/308) ([makasim](https://github.com/makasim))
- \[1.0\]\[cache\]\[resolver\] Improve caches invalidation. [\#304](https://github.com/liip/LiipImagineBundle/pull/304) ([makasim](https://github.com/makasim))

## [v0.18.0](https://github.com/liip/LiipImagineBundle/tree/v0.18.0) (2014-01-29)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/v0.17.1...v0.18.0)

- added an "auto\_rotate" filter based on exif data [\#254](https://github.com/liip/LiipImagineBundle/pull/254) ([digitalkaoz](https://github.com/digitalkaoz))

## [v0.17.1](https://github.com/liip/LiipImagineBundle/tree/v0.17.1) (2014-01-24)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/v0.17.0...v0.17.1)

- fixed missing namespace [\#306](https://github.com/liip/LiipImagineBundle/pull/306) ([digitalkaoz](https://github.com/digitalkaoz))
- \[1.0\]\[cache\] cache manager has to use isStored inside getBrowserPath method [\#303](https://github.com/liip/LiipImagineBundle/pull/303) ([makasim](https://github.com/makasim))
- \[1.0\]\[CacheResolver\] Use binary on store method call. [\#301](https://github.com/liip/LiipImagineBundle/pull/301) ([makasim](https://github.com/makasim))
- \[1.0\]\[filter-manager\] make use of binary object. [\#297](https://github.com/liip/LiipImagineBundle/pull/297) ([makasim](https://github.com/makasim))
- \[1.0\]\[loader\] remove deprecated phpcr loader [\#292](https://github.com/liip/LiipImagineBundle/pull/292) ([makasim](https://github.com/makasim))
- \[1.0\] Rework data loaders. Introduce mime type guesser.  [\#291](https://github.com/liip/LiipImagineBundle/pull/291) ([makasim](https://github.com/makasim))
- \[1.0\]\[tests\] increase code coverage by tests. [\#290](https://github.com/liip/LiipImagineBundle/pull/290) ([makasim](https://github.com/makasim))
- \[1.0\]\[Logger\] use PSR one logger [\#286](https://github.com/liip/LiipImagineBundle/pull/286) ([makasim](https://github.com/makasim))
- \[1.0\]\[CacheResolver\] Resolver get rid of get browser path [\#284](https://github.com/liip/LiipImagineBundle/pull/284) ([makasim](https://github.com/makasim))
- \[tests\] use real amazon libs in tests. [\#283](https://github.com/liip/LiipImagineBundle/pull/283) ([makasim](https://github.com/makasim))
- \[1.0\]\[resolver\] do not expose `targetPath` [\#282](https://github.com/liip/LiipImagineBundle/pull/282) ([makasim](https://github.com/makasim))
- \[1.0\]\[resolver\] remove request parameter [\#281](https://github.com/liip/LiipImagineBundle/pull/281) ([makasim](https://github.com/makasim))

## [v0.17.0](https://github.com/liip/LiipImagineBundle/tree/v0.17.0) (2013-12-04)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/v0.16.0...v0.17.0)

- handle image extensions in doctrine loader [\#276](https://github.com/liip/LiipImagineBundle/pull/276) ([dbu](https://github.com/dbu))
- Exclude Tests directory on composer archive [\#274](https://github.com/liip/LiipImagineBundle/pull/274) ([oziks](https://github.com/oziks))
- fix composer require-dev [\#272](https://github.com/liip/LiipImagineBundle/pull/272) ([havvg](https://github.com/havvg))
- Update filters.md [\#267](https://github.com/liip/LiipImagineBundle/pull/267) ([uwej711](https://github.com/uwej711))
- Add comment for image parameter in watermark filter configuration exampl... [\#263](https://github.com/liip/LiipImagineBundle/pull/263) ([USvER](https://github.com/USvER))

## [v0.16.0](https://github.com/liip/LiipImagineBundle/tree/v0.16.0) (2013-09-30)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/v0.15.1...v0.16.0)

- Add Upscale filter [\#248](https://github.com/liip/LiipImagineBundle/pull/248) ([maximecolin](https://github.com/maximecolin))

## [v0.15.1](https://github.com/liip/LiipImagineBundle/tree/v0.15.1) (2013-09-20)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/v0.15.0...v0.15.1)

- Set ContentType of AWS cache object [\#246](https://github.com/liip/LiipImagineBundle/pull/246) ([eXtreme](https://github.com/eXtreme))

## [v0.15.0](https://github.com/liip/LiipImagineBundle/tree/v0.15.0) (2013-09-18)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/v0.14.0...v0.15.0)

- deprecate the phpcr loader as CmfMediaBundle provides a better one now. [\#243](https://github.com/liip/LiipImagineBundle/pull/243) ([dbu](https://github.com/dbu))
- fix missing filename in exception [\#240](https://github.com/liip/LiipImagineBundle/pull/240) ([havvg](https://github.com/havvg))
- Corrected aws-sdk-php link [\#233](https://github.com/liip/LiipImagineBundle/pull/233) ([javiacei](https://github.com/javiacei))

## [v0.14.0](https://github.com/liip/LiipImagineBundle/tree/v0.14.0) (2013-08-21)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/v0.13.0...v0.14.0)

- add AwsS3Resolver for new SDK version [\#227](https://github.com/liip/LiipImagineBundle/pull/227) ([havvg](https://github.com/havvg))

## [v0.13.0](https://github.com/liip/LiipImagineBundle/tree/v0.13.0) (2013-08-19)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/v0.12.0...v0.13.0)

- Watermark loader [\#222](https://github.com/liip/LiipImagineBundle/pull/222) ([KingCrunch](https://github.com/KingCrunch))

## [v0.12.0](https://github.com/liip/LiipImagineBundle/tree/v0.12.0) (2013-08-19)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/v0.11.1...v0.12.0)

- Update dependency 'imagine/imagine' to 0.5.\* [\#221](https://github.com/liip/LiipImagineBundle/pull/221) ([KingCrunch](https://github.com/KingCrunch))

## [v0.11.1](https://github.com/liip/LiipImagineBundle/tree/v0.11.1) (2013-08-05)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/v0.11.0...v0.11.1)

- added documentation on inset and outbound modes of thumbnail filter Documentation \(issue \#207\) [\#210](https://github.com/liip/LiipImagineBundle/pull/210) ([rjbijl](https://github.com/rjbijl))

## [v0.11.0](https://github.com/liip/LiipImagineBundle/tree/v0.11.0) (2013-06-21)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/v0.10.1...v0.11.0)

- Add link filter [\#201](https://github.com/liip/LiipImagineBundle/pull/201) ([EmmanuelVella](https://github.com/EmmanuelVella))
- Thumbnail filter was not applied when allow\_upscale=true and one dimensi... [\#200](https://github.com/liip/LiipImagineBundle/pull/200) ([teohhanhui](https://github.com/teohhanhui))
- Add badge poser in README [\#199](https://github.com/liip/LiipImagineBundle/pull/199) ([agiuliano](https://github.com/agiuliano))
- add docs about allow\_scale of thumbnail filter [\#198](https://github.com/liip/LiipImagineBundle/pull/198) ([havvg](https://github.com/havvg))
- add documentation on S3 object URL options [\#197](https://github.com/liip/LiipImagineBundle/pull/197) ([havvg](https://github.com/havvg))

## [v0.10.1](https://github.com/liip/LiipImagineBundle/tree/v0.10.1) (2013-05-29)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/v0.10.0...v0.10.1)

- mkdir\(\) doesn't take care about the umask [\#189](https://github.com/liip/LiipImagineBundle/pull/189) ([KingCrunch](https://github.com/KingCrunch))
- The quickest PR to review I guess.  [\#188](https://github.com/liip/LiipImagineBundle/pull/188) ([Sydney-o9](https://github.com/Sydney-o9))

## [v0.10.0](https://github.com/liip/LiipImagineBundle/tree/v0.10.0) (2013-05-17)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/v0.9.4...v0.10.0)

- CacheResolver [\#184](https://github.com/liip/LiipImagineBundle/pull/184) ([havvg](https://github.com/havvg))
- fix broken tests on windows [\#179](https://github.com/liip/LiipImagineBundle/pull/179) ([kevinarcher](https://github.com/kevinarcher))

## [v0.9.4](https://github.com/liip/LiipImagineBundle/tree/v0.9.4) (2013-05-14)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/v0.9.3...v0.9.4)

- fix doc of CacheManager::resolve to not lie [\#186](https://github.com/liip/LiipImagineBundle/pull/186) ([dbu](https://github.com/dbu))
- Small documentation fix for getting browserPath for a thumb from controller [\#178](https://github.com/liip/LiipImagineBundle/pull/178) ([leberknecht](https://github.com/leberknecht))
- improve phpcr loader doc [\#177](https://github.com/liip/LiipImagineBundle/pull/177) ([dbu](https://github.com/dbu))
- Allow symfony 2.3 and greater [\#176](https://github.com/liip/LiipImagineBundle/pull/176) ([tommygnr](https://github.com/tommygnr))

## [v0.9.3](https://github.com/liip/LiipImagineBundle/tree/v0.9.3) (2013-04-17)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/v0.9.2...v0.9.3)

- add CacheManagerAwareTrait [\#173](https://github.com/liip/LiipImagineBundle/pull/173) ([havvg](https://github.com/havvg))

## [v0.9.2](https://github.com/liip/LiipImagineBundle/tree/v0.9.2) (2013-04-08)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/v0.9.1...v0.9.2)

- Add background filter [\#171](https://github.com/liip/LiipImagineBundle/pull/171) ([maxbeutel](https://github.com/maxbeutel))
- made the phpcr loader search for the requested path with or without a file extension [\#169](https://github.com/liip/LiipImagineBundle/pull/169) ([lsmith77](https://github.com/lsmith77))
- use composer require command [\#160](https://github.com/liip/LiipImagineBundle/pull/160) ([gimler](https://github.com/gimler))
- Update installation.md [\#159](https://github.com/liip/LiipImagineBundle/pull/159) ([dlondero](https://github.com/dlondero))
- Update README.md [\#158](https://github.com/liip/LiipImagineBundle/pull/158) ([dlondero](https://github.com/dlondero))

## [v0.9.1](https://github.com/liip/LiipImagineBundle/tree/v0.9.1) (2013-02-20)
[Full Changelog](https://github.com/liip/LiipImagineBundle/compare/v0.9.0...v0.9.1)

- added the 'strip' filter [\#152](https://github.com/liip/LiipImagineBundle/pull/152) ([uwej711](https://github.com/uwej711))

## [v0.9.0](https://github.com/liip/LiipImagineBundle/tree/v0.9.0) (2013-02-13)
- add FilterManager::applyFilter [\#150](https://github.com/liip/LiipImagineBundle/pull/150) ([havvg](https://github.com/havvg))
- add "Introduction" chapter to documentation [\#149](https://github.com/liip/LiipImagineBundle/pull/149) ([havvg](https://github.com/havvg))
- split documentation and README into chapters [\#148](https://github.com/liip/LiipImagineBundle/pull/148) ([havvg](https://github.com/havvg))
- Add route options to routing loader [\#138](https://github.com/liip/LiipImagineBundle/pull/138) ([sveriger](https://github.com/sveriger))
- Added a data loader for PHPCR [\#134](https://github.com/liip/LiipImagineBundle/pull/134) ([Burgov](https://github.com/Burgov))
- minor cleanup [\#133](https://github.com/liip/LiipImagineBundle/pull/133) ([havvg](https://github.com/havvg))
- Add image form type [\#130](https://github.com/liip/LiipImagineBundle/pull/130) ([EmmanuelVella](https://github.com/EmmanuelVella))
- New minor Imagine version [\#129](https://github.com/liip/LiipImagineBundle/pull/129) ([jcrombez](https://github.com/jcrombez))
- Pathinfo-related notices in generateUrl\(\) [\#128](https://github.com/liip/LiipImagineBundle/pull/128) ([thanosp](https://github.com/thanosp))
- Updated the Imagine library to version 0.4.0 [\#127](https://github.com/liip/LiipImagineBundle/pull/127) ([ubick](https://github.com/ubick))
- Added some documentation to Outside the web root chapter [\#122](https://github.com/liip/LiipImagineBundle/pull/122) ([nass600](https://github.com/nass600))
- Added PasteFilterLoader [\#118](https://github.com/liip/LiipImagineBundle/pull/118) ([lmcd](https://github.com/lmcd))
- add info on the StreamWrapper of GaufretteBundle [\#115](https://github.com/liip/LiipImagineBundle/pull/115) ([havvg](https://github.com/havvg))
- Properly set config parameter in the container [\#113](https://github.com/liip/LiipImagineBundle/pull/113) ([kevinarcher](https://github.com/kevinarcher))
- Adding cache directory permissions configuration parameter [\#112](https://github.com/liip/LiipImagineBundle/pull/112) ([kevinarcher](https://github.com/kevinarcher))
- Renamed "auto\_clear\_cache" to "cache\_clearer" [\#102](https://github.com/liip/LiipImagineBundle/pull/102) ([Spea](https://github.com/Spea))
- Added option to disable cache\_clearer [\#101](https://github.com/liip/LiipImagineBundle/pull/101) ([Spea](https://github.com/Spea))
- Cache resolver service argument order in readme [\#100](https://github.com/liip/LiipImagineBundle/pull/100) ([johnnypeck](https://github.com/johnnypeck))
- Added GridFS Loader [\#99](https://github.com/liip/LiipImagineBundle/pull/99) ([jdewit](https://github.com/jdewit))
- Update composer.json [\#95](https://github.com/liip/LiipImagineBundle/pull/95) ([krispypen](https://github.com/krispypen))
- Use the basePath in the file path resolver \(useful in "\_dev" or "\_\*" env... [\#92](https://github.com/liip/LiipImagineBundle/pull/92) ([khepin](https://github.com/khepin))
- add basePath injection to filesystem resolver [\#91](https://github.com/liip/LiipImagineBundle/pull/91) ([havvg](https://github.com/havvg))
- add "using the controller as a service" to the documentation [\#88](https://github.com/liip/LiipImagineBundle/pull/88) ([inmarelibero](https://github.com/inmarelibero))
- minor fix in readme [\#87](https://github.com/liip/LiipImagineBundle/pull/87) ([stefax](https://github.com/stefax))
- ensure that hardcoded filter formats are applied [\#86](https://github.com/liip/LiipImagineBundle/pull/86) ([lsmith77](https://github.com/lsmith77))
- fixed \#81 cache clearer only registered for sf2.1 [\#82](https://github.com/liip/LiipImagineBundle/pull/82) ([digitalkaoz](https://github.com/digitalkaoz))
- Issue 43 - Added a cache clearer for generated images [\#80](https://github.com/liip/LiipImagineBundle/pull/80) ([sixty-nine](https://github.com/sixty-nine))
- added NoCacheResolver [\#76](https://github.com/liip/LiipImagineBundle/pull/76) ([ghost](https://github.com/ghost))
- Fixed errors in README.md [\#75](https://github.com/liip/LiipImagineBundle/pull/75) ([iamdto](https://github.com/iamdto))
- add LoggerInterface to AmazonS3Resolver [\#70](https://github.com/liip/LiipImagineBundle/pull/70) ([havvg](https://github.com/havvg))
- fix AmazonS3Resolver [\#69](https://github.com/liip/LiipImagineBundle/pull/69) ([havvg](https://github.com/havvg))
- several fixes to the AmazonS3Resolver based on feedback [\#68](https://github.com/liip/LiipImagineBundle/pull/68) ([havvg](https://github.com/havvg))
- move getFilePath to AbstractFilesystemResolver [\#67](https://github.com/liip/LiipImagineBundle/pull/67) ([havvg](https://github.com/havvg))
- add AmazonS3Resolver and ResolverInterface::remove [\#66](https://github.com/liip/LiipImagineBundle/pull/66) ([havvg](https://github.com/havvg))
- Throwing an error if source image doesn't exist [\#65](https://github.com/liip/LiipImagineBundle/pull/65) ([fixe](https://github.com/fixe))
- add GaufretteFilesystemLoader [\#63](https://github.com/liip/LiipImagineBundle/pull/63) ([havvg](https://github.com/havvg))
- Mark image services as non public [\#62](https://github.com/liip/LiipImagineBundle/pull/62) ([lstrojny](https://github.com/lstrojny))
- Updates PdfTransformer so that imagick is injected [\#61](https://github.com/liip/LiipImagineBundle/pull/61) ([lucasaba](https://github.com/lucasaba))
- add crop filter; add missing option for thumbnail filter [\#58](https://github.com/liip/LiipImagineBundle/pull/58) ([gimler](https://github.com/gimler))
- Add file transformers to the file loader [\#57](https://github.com/liip/LiipImagineBundle/pull/57) ([lucasaba](https://github.com/lucasaba))
- Use of protected class properties in FilesystemLoader [\#54](https://github.com/liip/LiipImagineBundle/pull/54) ([petrjaros](https://github.com/petrjaros))
- 'cache\_resolver' property name change [\#53](https://github.com/liip/LiipImagineBundle/pull/53) ([petrjaros](https://github.com/petrjaros))
- add composer.json [\#51](https://github.com/liip/LiipImagineBundle/pull/51) ([iampersistent](https://github.com/iampersistent))
- Fix for last version of symfony [\#50](https://github.com/liip/LiipImagineBundle/pull/50) ([benji07](https://github.com/benji07))
- Allowed a file extension to be inferred for source files without one [\#47](https://github.com/liip/LiipImagineBundle/pull/47) ([web-dev](https://github.com/web-dev))
- Added a configuration option for the data root. [\#46](https://github.com/liip/LiipImagineBundle/pull/46) ([web-dev](https://github.com/web-dev))
- README update: source img outside web root [\#45](https://github.com/liip/LiipImagineBundle/pull/45) ([scoolen](https://github.com/scoolen))
- Fixing typo in README.md [\#44](https://github.com/liip/LiipImagineBundle/pull/44) ([stefanosala](https://github.com/stefanosala))
- update template extension and helper names [\#41](https://github.com/liip/LiipImagineBundle/pull/41) ([iampersistent](https://github.com/iampersistent))
- Refactor RelativeResize code and add documentation [\#39](https://github.com/liip/LiipImagineBundle/pull/39) ([jmikola](https://github.com/jmikola))
- Add Resize and RelativeResize filters [\#37](https://github.com/liip/LiipImagineBundle/pull/37) ([jmikola](https://github.com/jmikola))
- Extracted the abstract class Resolver from WebPathResolver [\#35](https://github.com/liip/LiipImagineBundle/pull/35) ([sixty-nine](https://github.com/sixty-nine))
- fix service name [\#34](https://github.com/liip/LiipImagineBundle/pull/34) ([lenar](https://github.com/lenar))
- Removed webRoot logic outside controller [\#28](https://github.com/liip/LiipImagineBundle/pull/28) ([LouTerrailloune](https://github.com/LouTerrailloune))
- Fixed redirect using wrong variable [\#27](https://github.com/liip/LiipImagineBundle/pull/27) ([Spea](https://github.com/Spea))
- Tweak response creation [\#26](https://github.com/liip/LiipImagineBundle/pull/26) ([lsmith77](https://github.com/lsmith77))
- fixed unit tests, fixes GH-22 [\#24](https://github.com/liip/LiipImagineBundle/pull/24) ([ghost](https://github.com/ghost))
- added missing docblock [\#20](https://github.com/liip/LiipImagineBundle/pull/20) ([LouTerrailloune](https://github.com/LouTerrailloune))
- allow-all default setting for liip\_imagine.formats [\#14](https://github.com/liip/LiipImagineBundle/pull/14) ([ghost](https://github.com/ghost))
- added support for many filter transformations in one filter set \(style\), fixes GH-1 [\#11](https://github.com/liip/LiipImagineBundle/pull/11) ([ghost](https://github.com/ghost))
- fixed ImagineLoader - cache prefix was not used in urls [\#6](https://github.com/liip/LiipImagineBundle/pull/6) ([ghost](https://github.com/ghost))
- fixed CachePathResolver\#getBrowserPath [\#5](https://github.com/liip/LiipImagineBundle/pull/5) ([ghost](https://github.com/ghost))
- Added check for the existence of extension info [\#147](https://github.com/liip/LiipImagineBundle/pull/147) ([thanosp](https://github.com/thanosp))
- add Tests for bundle features [\#140](https://github.com/liip/LiipImagineBundle/pull/140) ([havvg](https://github.com/havvg))



\* *This Change Log was automatically generated by [github_changelog_generator](https://github.com/skywinder/Github-Changelog-Generator)*