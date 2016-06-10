#!/bin/bash

###############################################################################
#
# Utility for testing REST API requests.
#
###############################################################################
#
# 1) Generates a new nonce via make_sig.php
# 2) Generates a signature via make_sig.php
# 3) Executes a request via curl using the nonce and signature generated above.
#
###############################################################################

function usage() {
	echo "Usage: $0 <Verb> <URL> <Token> <TokenKey> [Body]"
	echo "Example: $0 GET 192.168.0.6/admin/rest.php/rest/findmefollow/users b479bd22add4b57e3cefbdcdfb37c8e81d51c607 69932febdc125aca93e422cadb677a8d90f8fda2"
	echo ""
	echo "Verb: GET|PUT|POST|DELETE"
	echo "URL: The URL for the resource."
	echo "Token: A token with access to the resource."
	echo "TokenKey: The key for the token."
	echo "Body: The body of the message, for a POST/PUT."
}

if [ -z "$1" -o -z "$2" -o -z "$3" -o -z "$4" ]; then
	usage
	exit 1
fi

VERB=$1
URL=$2
TOKEN=$3
TOKENKEY=$4
[ -n "$5" ] && BODY=$5

# Grab a new nonce for every request.
NONCE=$(php make_sig.php --action nonce | awk '/^Nonce: / {print $2}')

# Calculate a signature, to send along with the request.
# We strip the protocol from the URL, so we get a valid signature.
SIGNATURE=$(php make_sig.php --verb ${VERB} --url ${URL#*://} --token ${TOKEN} --tokenkey ${TOKENKEY} --nonce ${NONCE} --body "${BODY}"| awk '/^Signature: / {print $2}')

case ${VERB} in
GET|get)
	curl ${URL} --header "Signature: ${SIGNATURE}" --header "Nonce: ${NONCE}" --header "Token: ${TOKEN}" 2>/dev/null
	;;
PUT|put)
	curl ${URL} --header "Signature: ${SIGNATURE}" --header "Nonce: ${NONCE}" --header "Token: ${TOKEN}" --header "Content-Type: application/json" --data "${BODY}" -X PUT 2>/dev/null
	;;
POST|post)
	curl ${URL} --header "Signature: ${SIGNATURE}" --header "Nonce: ${NONCE}" --header "Token: ${TOKEN}" --header "Content-Type: application/json" --data "${BODY}" -X POST 2>/dev/null
	;;
DELETE|delete)
	# Untested!
	curl ${URL} --header "Signature: ${SIGNATURE}" --header "Nonce: ${NONCE}" --header "Token: ${TOKEN}" -X DELETE 2>/dev/null
	;;
*)
	echo "Verb '${VERB}' is currently unsupported."
	;;
esac

