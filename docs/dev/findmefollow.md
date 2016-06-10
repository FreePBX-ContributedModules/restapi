Find Me Follow Me
===

# Overview of Find Me Follow Me

Find Me Follow Me is a personal mini ringgroup tied directly to your extension. This way you can receive calls even when your out of the office, by simulatenously ringing your cellphone when someone calls your extension. 

# Find Me Follow Me API

## List All Find Me Follow Me Users
    
    GET admin/index.php/rest/findmefollow/users

### Response
```{
    "4003": "Bryan"
}```

## Get Individual Find Me Follow Me User

    GET admin/index.php/rest/findmefollow/users/:id

### Response
```{
    "grpnum": "4003",
    "strategy": "ringallv2",
    "grptime": "20",
    "grppre": "53",
    "grplist": "4003",
    "annmsg_id": "4",
    "postdest": "ext-local,vmu4003,1",
    "dring": "asga",
    "ddial": "DISABLED",
    "remotealert_id": "0",
    "toolate_id": "0",
    "ringing": "Ring",
    "pre_ring": "4",
    "voicemail": "default",
    "changecid": false,
    "fixedcid": "756483"
}```

## Add or modify Individual Find Me Follow Me

    PUT admin/index.php/rest/findmefollow/users/:id

### Parameters

strategy (**Ring Strategy**)
: *string* - Valid options are as follows:
1. ringallv2 - ring primary extension for initial ring time followed by all additional extensions until one answers
2. ringall - ring all available channels until one answers (default)
3. hunt - take turns ringing each available extension
4. memoryhunt - ring first extension     in the list, then ring the 1st and 2nd extension, then ring 1st 2nd and 3rd extension in the list.... etc.
5. *-prim - these modes act as described above. However, if the primary extension (first in list) is             occupied, the other extensions will not be rung. If the primary is FreePBX DND, it won't be rung. If the primary is FreePBX CF unconditional, then all will be rung
6. firstavailable - ring only the first available channel
7. firstnotonphone - ring only      the first channel which is not off hook - ignore CW

grptime (**Ring Time(max 60 sec)**)
: *integer* - Time in seconds that the phones will ring. For all hunt style ring strategies, this is the time for each iteration of phone(s) that are rung

grplist (**Follow-Me List**)
: *string* - List extensions to ring, one per line, or use the Extension Quick Pick below. You can include an    extension on a remote system, or an external number by suffixing a number with a pound (#). ex: 2448089# would dial 2448089 on the appropriate trunk (see Outbound Routing)

postdest
: postdest description goes here

grppre (**CID Name Prefix**)
: _Optional_ *string* - You can optionally prefix the Caller ID name when ringing extensions in this group. ie: If you prefix with "Sales:", a call from John Doe would display as "Sales:John Doe" on the extensions that ring.

annmsg\_id (**Announcement**)
: _Optional_ *integer* - Message to be played to the caller before dialing this group. To add additional recordings please use the "System Recordings" MENU to the left

dring (**Alert Info**)
: *string* - You can optionally include an Alert Info which can create distinctive rings on SIP phones.

needsconf (**Confirm Calls**)
: *boolean* - Enable this if you're calling external numbers that need confirmation - eg, a mobile phone may go to voicemail which will pick up the call. Enabling this requires the remote side push 1 on their phone before the call is put through. This feature only works with the ringall/ringall-prim  ring strategy 

remotealert_id (**Remote Announce**)
: *integer* - Message to be played to the person RECEIVING the call, if 'Confirm Calls' is enabled. To add additional recordings use the "System Recordings" MENU to the left

toolate_id (**Too-Late Announce**)
: *integer* - Message to be played to the person RECEIVING the call, if the call has already been accepted before they push 1. To add additional recordings use the "System Recordings" MENU to the left

ringing (**Play Music On Hold**)
: *string* - If you select a Music on Hold class to play, instead of 'Ring', they will hear that instead of Ringing while they are waiting for someone to pick up.

pre_ring (**Initial Ring Time**)
: *integer* - This is the number of seconds to ring the primary extension prior to proceeding to the follow-me list. The extension can also be included in the follow-me list. A 0 setting will bypass this.

ddial (**Disable**)
: *boolean* - By default (not checked) any call to this extension will go to this Follow-Me instead, including directory calls by name from IVRs. If checked, calls will go only to the extension.<BR>However, destinations that specify FollowMe will come here.<BR>Checking this box is often used in conjunction with VmX Locater, where you want a call to ring the extension, and then only if the caller chooses to find you do you want it to come here.

changecid (**Dhange External CID Configuration**)
: _Optional_ *string* - Valid options are as follows\:
1. default - Transmits the Callers CID if allowed by the trunk.
2. fixed - Always transmit the Fixed CID Value below.
3. extern - Transmit the Fixed CID Value below on calls that come in from outside only. Internal extension to extension calls will continue to operate in default mode.
4. did - Transmit the number that was dialed as the CID for calls coming from outside. Internal extension to extension calls will continue to operate in default mode. There must be a DID on the inbound route for this. This will be BLOCKED on trunks that block foreign CallerID
5. forcedid - Transmit the number that was dialed as the CID for calls coming from outside. Internal extension to extension calls will continue to operate in default mode. There must be a DID on the inbound route for this. This WILL be transmitted on trunks that block foreign CallerID

fixedcid (**Fixed CID Value**)
: _Optional_ *integer* - Fixed value to replace the CID with used with some of the modes above. Should be in a format of digits only with an option of E164 format using a leading "+".

### Response
```
Response goes here
```

