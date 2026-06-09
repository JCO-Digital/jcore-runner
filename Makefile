.PHONY: dev ci ci-setup setup release start stop clean

dev: setup

ci: ci-setup

ci-setup:
	pnpm i
	composer install --no-dev --no-interaction --optimize-autoloader

setup:
	pnpm i
	composer install

release: ci
	mkdir -p release
	zip release/jcore-runner.zip -r * -x@.zipexclude

start:
	pnpm run env:start

stop:
	pnpm run env:stop

clean:
	rm -rf node_modules
	rm -rf vendor
	rm -rf build
	rm -rf release
